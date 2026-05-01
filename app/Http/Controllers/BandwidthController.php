<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use App\Models\BandwidthProfile;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class BandwidthController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Afficher la gestion des profils de bande passante
     */
    public function index(Routeur $routeur)
    {
        $profiles = $routeur->bandwidthProfiles()
            ->orderBy('priority')
            ->orderBy('nom')
            ->get();

        // Profils par défaut suggérés si aucun
        $defaultProfiles = [
            ['nom' => 'Direction', 'download' => 50, 'upload' => 20, 'quota' => 0, 'color' => 'purple'],
            ['nom' => 'Techniciens', 'download' => 10, 'upload' => 5, 'quota' => 50, 'color' => 'cyan'],
            ['nom' => 'Stagiaires', 'download' => 3, 'upload' => 1, 'quota' => 10, 'color' => 'amber'],
            ['nom' => 'Invités', 'download' => 1, 'upload' => 0.5, 'quota' => 2, 'color' => 'rose'],
        ];

        return view('reseau.bandwidth', compact('routeur', 'profiles', 'defaultProfiles'));
    }

    /**
     * Créer un nouveau profil de bande passante
     */
    public function store(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'download_mbps' => 'required|integer|min:0|max:10000',
            'upload_mbps' => 'required|integer|min:0|max:10000',
            'quota_gb' => 'nullable|integer|min:0',
            'target_network' => 'nullable|string|max:50',
            'priority' => 'required|integer|min:1|max:8',
            'color' => 'required|in:blue,emerald,amber,rose,purple,cyan,indigo',
        ]);

        $profile = new BandwidthProfile([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'download_mbps' => $validated['download_mbps'],
            'upload_mbps' => $validated['upload_mbps'],
            'quota_gb' => $validated['quota_gb'] ?? null,
            'target_network' => $validated['target_network'] ?? null,
            'priority' => $validated['priority'],
            'color' => $validated['color'],
            'active' => true,
        ]);

        $routeur->bandwidthProfiles()->save($profile);

        // Appliquer sur MikroTik si en ligne
        $syncOk = false;
        if ($routeur->statut === 'en_ligne') {
            $syncOk = $this->applyProfileToMikrotik($routeur, $profile);
        }

        $msg = 'Profil "' . $profile->nom . '" créé avec succès';
        if (!$syncOk && $routeur->statut === 'en_ligne') {
            $msg .= ' (synchronisation MikroTik échouée)';
        } elseif ($routeur->statut !== 'en_ligne') {
            $msg .= ' (routeur hors ligne)';
        }

        return redirect()->route('admin-reseau.bandwidth', $routeur)
            ->with('success', $msg);
    }

    /**
     * Mettre à jour un profil
     */
    public function update(Request $request, Routeur $routeur, BandwidthProfile $profile)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'download_mbps' => 'required|integer|min:0|max:10000',
            'upload_mbps' => 'required|integer|min:0|max:10000',
            'quota_gb' => 'nullable|integer|min:0',
            'target_network' => 'nullable|string|max:50',
            'priority' => 'required|integer|min:1|max:8',
            'color' => 'required|in:blue,emerald,amber,rose,purple,cyan,indigo',
            'active' => 'boolean',
        ]);

        $profile->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'download_mbps' => $validated['download_mbps'],
            'upload_mbps' => $validated['upload_mbps'],
            'quota_gb' => $validated['quota_gb'] ?? null,
            'target_network' => $validated['target_network'] ?? null,
            'priority' => $validated['priority'],
            'color' => $validated['color'],
            'active' => $validated['active'] ?? true,
        ]);

        // Resynchroniser avec MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->applyProfileToMikrotik($routeur, $profile);
        }

        return redirect()->route('admin-reseau.bandwidth', $routeur)
            ->with('success', 'Profil "' . $profile->nom . '" mis à jour');
    }

    /**
     * Supprimer un profil
     */
    public function destroy(Routeur $routeur, BandwidthProfile $profile)
    {
        $nom = $profile->nom;

        // Supprimer la queue sur MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->mikrotik->removeQueueByName($routeur, $profile->getQueueName());
        }

        $profile->delete();

        return redirect()->route('admin-reseau.bandwidth', $routeur)
            ->with('success', 'Profil "' . $nom . '" supprimé');
    }

    /**
     * Activer/Désactiver un profil
     */
    public function toggle(Routeur $routeur, BandwidthProfile $profile)
    {
        $profile->active = !$profile->active;
        $profile->save();

        if ($routeur->statut === 'en_ligne') {
            if ($profile->active) {
                $this->applyProfileToMikrotik($routeur, $profile);
            } else {
                $this->mikrotik->removeQueueByName($routeur, $profile->getQueueName());
            }
        }

        $status = $profile->active ? 'activé' : 'désactivé';
        return redirect()->route('admin-reseau.bandwidth', $routeur)
            ->with('success', 'Profil "' . $profile->nom . '" ' . $status);
    }

    /**
     * Appliquer un profil sur MikroTik (créer/modifier la queue)
     */
    private function applyProfileToMikrotik(Routeur $routeur, BandwidthProfile $profile): bool
    {
        if (!$profile->active) {
            return true;
        }

        $target = $profile->target_network;
        if (!$target) {
            // Si pas de réseau spécifié, on ne peut pas créer la queue
            return false;
        }

        return $this->mikrotik->setBandwidthQueue(
            $routeur,
            $profile->getQueueName(),
            $target,
            $profile->download_mbps,
            $profile->upload_mbps
        );
    }

    /**
     * Obtenir les détails d'un profil (AJAX)
     */
    public function show(Routeur $routeur, BandwidthProfile $profile)
    {
        return response()->json([
            'profile' => $profile,
            'bandwidth_formatted' => $profile->bandwidthFormatted(),
            'quota_formatted' => $profile->quotaFormatted(),
            'queue_name' => $profile->getQueueName(),
            'mikrotik_limit' => $profile->getMikrotikMaxLimit(),
        ]);
    }

    /**
     * Générer les commandes RouterOS pour un profil (AJAX)
     */
    public function getCommands(Routeur $routeur, BandwidthProfile $profile)
    {
        $commands = [];

        $commands[] = '# Profil: ' . $profile->nom;
        $commands[] = '# Description: ' . ($profile->description ?: 'N/A');
        $commands[] = '# Bande passante: ' . $profile->bandwidthFormatted();
        $commands[] = '# Quota: ' . $profile->quotaFormatted();
        $commands[] = '';
        $commands[] = '# Créer la queue simple';

        if ($profile->target_network) {
            $commands[] = '/queue simple add name=' . $profile->getQueueName() . 
                          ' target=' . $profile->target_network . 
                          ' max-limit=' . $profile->getMikrotikMaxLimit() . 
                          ' comment="Profil: ' . $profile->nom . '"';
        } else {
            $commands[] = '# ERREUR: Aucun réseau cible défini pour ce profil';
        }

        // Commandes pour afficher les queues existantes
        $commands[] = '';
        $commands[] = '# Vérifier les queues existantes';
        $commands[] = '/queue simple print where name~"profil_"';

        return response()->json(['commands' => $commands]);
    }

    /**
     * Appliquer tous les profils actifs sur MikroTik
     */
    public function applyAll(Request $request, Routeur $routeur)
    {
        if ($routeur->statut !== 'en_ligne') {
            return redirect()->route('admin-reseau.bandwidth', $routeur)
                ->with('error', 'Le routeur doit être en ligne pour appliquer les profils');
        }

        $profiles = $routeur->bandwidthProfiles()->where('active', true)->get();
        $applied = 0;
        $failed = 0;

        foreach ($profiles as $profile) {
            if ($this->applyProfileToMikrotik($routeur, $profile)) {
                $applied++;
            } else {
                $failed++;
            }
        }

        $msg = $applied . ' profil(s) appliqué(s)';
        if ($failed > 0) {
            $msg .= ', ' . $failed . ' échec(s)';
        }

        return redirect()->route('admin-reseau.bandwidth', $routeur)
            ->with('success', $msg);
    }
}
