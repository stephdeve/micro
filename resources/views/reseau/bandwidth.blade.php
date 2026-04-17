@extends('layouts.app')

@section('title', 'Bande Passante - ' . $routeur->nom)

@section('content')
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> Gestion de Bande Passante</h1>
        <p>Routeur : {{ $routeur->nom }} ({{ $routeur->adresse_ip }})</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-stream"></i> Queues Simples</h3>
            <button class="btn btn-primary btn-sm" onclick="openModal()">
                <i class="fas fa-plus"></i> Ajouter une queue
            </button>
        </div>
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Cible</th>
                        <th>Max Limit</th>
                        <th>Limit At</th>
                        <th>Burst Limit</th>
                        <th>Commentaire</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues ?? [] as $queue)
                        <tr class="{{ $queue['disabled'] ? 'disabled' : '' }}">
                            <td>{{ $queue['name'] }}</td>
                            <td><code>{{ $queue['target'] }}</code></td>
                            <td><span class="limit-badge">{{ $queue['max_limit'] }}</span></td>
                            <td>{{ $queue['limit_at'] ?? '—' }}</td>
                            <td>{{ $queue['burst_limit'] ?? '—' }}</td>
                            <td>{{ $queue['comment'] ?? '—' }}</td>
                            <td>
                                <span class="status-badge {{ $queue['disabled'] ? 'status-off' : 'status-on' }}">
                                    {{ $queue['disabled'] ? 'Désactivé' : 'Actif' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-edit" onclick="editQueue('{{ $queue['id'] }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteQueue('{{ $queue['id'] }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Aucune queue configurée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="info-box">
        <h4><i class="fas fa-info-circle"></i> Format des limites</h4>
        <p>Le format attendu pour les limites est : <code>upload/download</code> (ex: <code>10M/50M</code>)</p>
        <ul>
            <li><strong>k</strong> = kilo bits</li>
            <li><strong>M</strong> = mega bits</li>
            <li><strong>G</strong> = giga bits</li>
        </ul>
    </div>
</div>

<script>
    function openModal() {
        alert('Formulaire d\'ajout de queue');
    }
    function editQueue(id) {
        alert('Modifier queue ' + id);
    }
    function deleteQueue(id) {
        if (!confirm('Supprimer cette queue ?')) return;
        fetch(`{{ route('admin-reseau.bandwidth.destroy', [$routeur, '']) }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(r => r.json()).then(d => location.reload());
    }
</script>

<style>
    .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 1.5rem; }
    .card-header { padding: 1rem 1.2rem; border-bottom: 1px solid #eee; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center; }
    .card-header h3 { margin: 0; font-size: 1rem; }
    .card-body { padding: 1.2rem; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 0.6rem; text-align: left; border-bottom: 1px solid #eee; }
    .data-table th { font-weight: 600; background: #f8f9fa; }
    .data-table code { background: #f0f0f0; padding: 0.2rem 0.4rem; border-radius: 4px; font-family: monospace; }
    .disabled { opacity: 0.5; background: #f5f5f5; }
    .limit-badge { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
    .status-badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
    .status-on { background: #d4edda; color: #155724; }
    .status-off { background: #f8d7da; color: #721c24; }
    .btn { padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; }
    .btn-primary { background: #3498db; color: #fff; }
    .btn-sm { font-size: 0.85rem; padding: 0.3rem 0.6rem; }
    .btn-edit { background: #f39c12; color: #fff; }
    .btn-danger { background: #e74c3c; color: #fff; }
    .text-center { text-align: center; }
    .info-box { background: #e8f4fd; border-left: 4px solid #3498db; padding: 1rem; border-radius: 0 8px 8px 0; }
    .info-box h4 { margin: 0 0 0.5rem 0; color: #2c3e50; }
    .info-box code { background: #fff; padding: 0.2rem 0.4rem; border-radius: 4px; }
    .info-box ul { margin: 0.5rem 0 0 1rem; }
</style>
@endsection
