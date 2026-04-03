@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>
    @include('layouts.guest')

    <!-- KPIs principaux -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-tachometer-alt"></i> Trafic total<br><span style="font-size:0.75rem;color:#8ba9d0">24 dernières heures</span></div>
            <div class="stat-value">{{ number_format($totalTrafficTb ?? 0, 2) }} TB</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> {{ $totalTrafficTb ? round(($totalTrafficTb / 1.2 - 1) * 100, 2) : 0 }}% vs hier</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-users"></i> Utilisateurs uniques</div>
            <div class="stat-value">{{ $activeUsers ?? 0 }}</div>
            <div class="stat-change">Moyenne {{ $activeUsers ? round($activeUsers * 0.95) : 0 }} actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-clock"></i> Disponibilité</div>
            <div class="stat-value">{{ $availability ?? 0 }}%</div>
            <div class="stat-change">{{ $totalRouteurs ?? 0 }} routeurs monitorés</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-chart-line"></i> Pic de trafic</div>
            <div class="stat-value">{{ $peakTraffic ? round($peakTraffic / 1024, 2) : 0 }} Mbps</div>
            <div class="stat-change">Dernier 24h</div>
        </div>
    </div>

    <!-- Graphiques principaux -->
    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Trafic entrant/sortant</h3>
                <div>
                    <span class="status-badge">en temps réel</span>
                </div>
            </div>
            <div style="height: 250px; display: flex; align-items: flex-end; gap: 2px; margin-top: 2rem;">
                @php
                    $maxHourly = max($hourlyTraffic ?? [0]);
                    $maxHourly = $maxHourly > 0 ? $maxHourly : 1;
                @endphp
                @foreach($hourlyTraffic ?? [] as $hour => $value)
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 2px; height: 200px;">
                        <div style="flex: 1; display: flex; flex-direction: column-reverse;">
                            <div style="height: {{ round(($value / $maxHourly) * 100, 2) }}%; background: linear-gradient(to top, #00ccff, #0099ff); width: 100%; border-radius: 4px 4px 0 0;"></div>
                        </div>
                        <div class="bar-label">{{ $hour }}h</div>
                    </div>
                @endforeach
            </div>
            <div style="display: flex; gap: 2rem; justify-content: center; margin-top: 1rem;">
                <div><span style="display: inline-block; width: 12px; height: 12px; background: #00ccff; border-radius: 3px;"></span> Trafic 24h</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Répartition du trafic</h3>
                <i class="fas fa-ellipsis-h"></i>
            </div>
            @php
                $totalDist = array_sum($trafficDistribution ?? []);
                $pieColors = ['web' => '#00ccff', 'streaming' => '#904eff', 'voip' => '#2ef75b', 'jeux' => '#ffaa33', 'autres' => '#ff5e7c'];
                $offset = 0;
                $pieStops = '';
            @endphp
            <div style="height: 250px; display: flex; justify-content: center; align-items: center;">
                @if($totalDist > 0)
                    @php
                        foreach($trafficDistribution as $type => $value) {
                            $pct = round(($value / $totalDist) * 100, 2);
                            $color = $pieColors[strtolower($type)] ?? '#7493b9';
                            $start = $offset;
                            $offset += $pct;
                            $pieStops .= "$color $start% $offset%, ";
                        }
                        $pieGradient = rtrim($pieStops, ', ');
                    @endphp
                    <div style="width: 180px; height: 180px; border-radius: 50%; background: conic-gradient({{ $pieGradient }});"></div>
                @else
                    <div style="width: 180px; height: 180px; border-radius: 50%; background: #1f2c40; display: flex; align-items: center; justify-content: center; color: #8ba9d0;">Aucune donnée</div>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 1rem;">
                @foreach($trafficDistribution as $type => $value)
                    @php
                        $pct = $totalDist > 0 ? round(($value / $totalDist) * 100, 1) : 0;
                        $color = $pieColors[strtolower($type)] ?? '#7493b9';
                    @endphp
                    <div><span style="display: inline-block; width: 12px; height: 12px; background: {{ $color }}; border-radius: 3px;"></span> {{ ucfirst($type) }} ({{ $pct }}%)</div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tableaux de statistiques détaillées -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.8rem; margin: 2rem 0;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-arrow-up"></i> Top interfaces (trafic)
                </h3>
            </div>
            <table style="width: 100%;">
                <tr><th>Interface</th><th>Traffic (KB)</th><th>%</th></tr>
                @php
                    $totalTop = $topEmitters->sum('value') ?: 1;
                @endphp
                @foreach($topEmitters as $emitter)
                    <tr>
                        <td>{{ $emitter['name'] }}</td>
                        <td>{{ number_format($emitter['value'], 2) }}</td>
                        <td>{{ number_format(($emitter['value'] / $totalTop) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-arrow-down"></i> Top interfaces (réception)
                </h3>
            </div>
            <table style="width: 100%;">
                <tr><th>Interface</th><th>Traffic (KB)</th><th>%</th></tr>
                @foreach($topEmitters as $emitter)
                    <tr>
                        <td>{{ $emitter['name'] }}</td>
                        <td>{{ number_format($emitter['value'], 2) }}</td>
                        <td>{{ number_format(($emitter['value'] / $totalTop) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection