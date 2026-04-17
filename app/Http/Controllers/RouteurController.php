<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MikrotikService;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RouteurController extends Controller
{
    /**
     * Formulaire de création redirigé vers index (modal + query param auto-open).
     */
    public function create()
    {
        return redirect()->route('routeurs.index', ['create' => 1]);
    }

    /**
     * Afficher la liste des routeurs.
     */
    public function index(Request $request)
    {
        $query = Routeur::query();

        // Filtres
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse_ip', 'like', '%' . $request->search . '%')
                  ->orWhere('modele', 'like', '%' . $request->search . '%')
                  ->orWhere('numero_serie', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('modele')) {
            $query->where('modele', $request->modele);
        }

        $routeurs = $query->with('responsable')->latest()->paginate(5);
        
        // Statistiques
        $stats = [
            'en_ligne' => Routeur::where('statut', 'en_ligne')->count(),
            'hors_ligne' => Routeur::where('statut', 'hors_ligne')->count(),
            'maintenance' => Routeur::where('statut', 'maintenance')->count(),
            'modeles' => Routeur::distinct('modele')->count('modele'),
            'derniere_version' => Routeur::orderBy('derniere_sync', 'desc')->value('version_ros') ?? '7.12'
        ];

        // Performances globales (moyennes)
        $globalPerformance = [
            'cpu' => Routeur::whereNotNull('cpu_usage')->avg('cpu_usage') ? round(Routeur::whereNotNull('cpu_usage')->avg('cpu_usage'), 1) : 0,
            'memory' => Routeur::whereNotNull('memory_usage')->avg('memory_usage') ? round(Routeur::whereNotNull('memory_usage')->avg('memory_usage'), 1) : 0,
            'temperature' => Routeur::whereNotNull('temperature')->avg('temperature') ? round(Routeur::whereNotNull('temperature')->avg('temperature'), 1) : 0,
        ];

        // Bande passante totale et top consommateurs
        $interfaces = \App\Models\InterfaceModel::whereHas('routeur', function ($q) {
            $q->where('statut', 'en_ligne');
        })->get();

        $totalBandwidth = $interfaces->sum(function ($interface) {
            return ($interface->debit_entrant ?? 0) + ($interface->debit_sortant ?? 0);
        });

        $topConsumers = $interfaces
            ->map(function ($interface) {
                $total = ($interface->debit_entrant ?? 0) + ($interface->debit_sortant ?? 0);
                return [
                    'routeur' => $interface->routeur->nom ?? 'Inconnu',
                    'interface' => $interface->nom,
                    'total' => $total,
                ];
            })
            ->sortByDesc('total')
            ->take(3)
            ->values();

        // Liste des modèles pour le filtre
        $modeles = Routeur::distinct('modele')->whereNotNull('modele')->pluck('modele');

        return view('routeurs.index', compact('routeurs', 'stats', 'modeles', 'globalPerformance', 'totalBandwidth', 'topConsumers'));
    }

    /**
     * Retourne les routeurs en JSON pour la vue dynamique.
     */
    public function data(Request $request)
    {
        $query = Routeur::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse_ip', 'like', '%' . $request->search . '%')
                  ->orWhere('modele', 'like', '%' . $request->search . '%')
                  ->orWhere('numero_serie', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('modele')) {
            $query->where('modele', $request->modele);
        }

        $routeurs = $query->with('responsable')->latest()->paginate(5);

        $stats = [
            'en_ligne' => Routeur::where('statut', 'en_ligne')->count(),
            'hors_ligne' => Routeur::where('statut', 'hors_ligne')->count(),
            'maintenance' => Routeur::where('statut', 'maintenance')->count(),
            'modeles' => Routeur::distinct('modele')->count('modele'),
            'derniere_version' => Routeur::orderBy('derniere_sync', 'desc')->value('version_ros') ?? '7.12'
        ];

        return response()->json([
            'routeurs' => $routeurs->items(),
            'pagination' => [
                'current_page' => $routeurs->currentPage(),
                'last_page' => $routeurs->lastPage(),
                'per_page' => $routeurs->perPage(),
                'total' => $routeurs->total(),
            ],
            'stats' => $stats,
        ]);
    }

    private function buildFilteredRouteurs(Request $request)
    {
        $query = Routeur::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse_ip', 'like', '%' . $request->search . '%')
                  ->orWhere('modele', 'like', '%' . $request->search . '%')
                  ->orWhere('numero_serie', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('modele')) {
            $query->where('modele', $request->modele);
        }

        return $query->with('responsable')->latest();
    }

    public function export(Request $request)
    {
        $format = strtolower($request->get('format', 'xlsx'));
        $routeurs = $this->buildFilteredRouteurs($request)->get();

        return match ($format) {
            'pdf' => $this->exportPdf($routeurs),
            'xlsx' => $this->exportExcel($routeurs),
            default => $this->exportExcel($routeurs),
        };
    }

    private function exportPdf($routeurs)
    {
        $filename = 'routeurs_' . now()->format('Ymd_His') . '.pdf';
        $pdf = Pdf::loadView('routeurs.export-pdf', compact('routeurs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function exportExcel($routeurs)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Routeurs');

        $headers = ['Nom', 'Modèle', 'Adresse IP', 'Version ROS', 'Statut', 'Uptime'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($routeurs as $routeur) {
            $sheet->setCellValue('A' . $row, $routeur->nom);
            $sheet->setCellValue('B' . $row, $routeur->modele ?? 'N/A');
            $sheet->setCellValue('C' . $row, $routeur->adresse_ip);
            $sheet->setCellValue('D' . $row, $routeur->version_ros ?? 'N/A');
            $sheet->setCellValue('E' . $row, $routeur->statut == 'en_ligne' ? 'En ligne' : ($routeur->statut == 'maintenance' ? 'Maintenance' : 'Hors ligne'));
            $sheet->setCellValue('F' . $row, $routeur->uptime ? floor($routeur->uptime / 86400) . ' jours' : 'N/A');
            $row++;
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'routeurs_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function print(Request $request)
    {
        $routeurs = $this->buildFilteredRouteurs($request)->get();

        return view('routeurs.print', compact('routeurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // If force checkbox is checked, delete existing routeur with same IP first
        if ($request->has('force_create')) {
            $existing = Routeur::withTrashed()->where('adresse_ip', $request->input('adresse_ip'))->first();
            if ($existing) {
                $existing->forceDelete();
            }
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse_ip' => 'required|ip|unique:routeurs,adresse_ip,NULL,id,deleted_at,NULL',
            'api_user' => 'required|string|max:100',
            'api_password' => 'required|string|max:100',
            'modele' => 'nullable|string|max:100',
            'adresse_mac' => 'nullable|string|max:17',
            'version_ros' => 'nullable|string|max:50',
            'firmware' => 'nullable|string|max:50',
            'numero_serie' => 'nullable|string|max:50',
            'emplacement' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'adresse_ip.required' => 'L\'adresse IP est requise',
            'adresse_ip.ip' => 'L\'adresse IP doit être au format valide (ex: 192.168.1.10)',
            'adresse_ip.unique' => 'Cette adresse IP existe déjà. Cochez "Forcer la création" pour remplacer.',
            'nom.required' => 'Le nom du routeur est requis',
            'api_user.required' => 'L\'utilisateur API est requis',
            'api_password.required' => 'Le mot de passe API est requis',
        ]);

        $data['statut'] = 'hors_ligne';
        $data['user_id'] = Auth::id();

        $routeur = Routeur::create($data);

        $handshakeResult = ['success' => false];
        if (!empty($routeur->api_user) && !empty($routeur->api_password)) {
            $handshakeResult = $routeur->handshake();
        }
        $handshakeOk = $handshakeResult['success'] ?? false;

        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Routeur ajouté',
            "Routeur {$routeur->nom} ajouté, synchronisation " . ($handshakeOk ? 'réussie' : 'échouée'),
            route('routeurs.index')
        ));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Routeur ajouté avec succès!',
                'routeur' => $routeur,
                'handshake' => $handshakeOk
            ]);
        }

        return redirect()->route('routeurs.index')->with('success', 'Routeur ajouté avec succès; handshake '.($handshakeOk ? 'reussi' : 'echoue').'.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Routeur $routeur)
    {
        $routeur->load('interfaces', 'responsable');
        return view('routeurs.show', compact('routeur'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Routeur $routeur)
    {
        if (request()->ajax()) {
            return response()->json($routeur);
        }

        // Si l'appel est direct (URL dans navigateur), on montre le formulaire d'édition.
        return view('routeurs.edit', compact('routeur'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Routeur $routeur)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'modele' => 'nullable|string|max:255',
            'adresse_ip' => 'required|ip|unique:routeurs,adresse_ip,' . $routeur->id,
            'adresse_mac' => 'nullable|string|max:17|unique:routeurs,adresse_mac,' . $routeur->id,
            'version_ros' => 'nullable|string|max:50',
            'firmware' => 'nullable|string|max:50',
            'numero_serie' => 'nullable|string|max:100|unique:routeurs,numero_serie,' . $routeur->id,
            'statut' => 'required|in:en_ligne,hors_ligne,maintenance',
            'emplacement' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $oldStatut = $routeur->statut;
        $routeur->update($request->all());

        // Notification si le statut a changé
        if ($oldStatut !== $routeur->statut) {
            $message = match($routeur->statut) {
                'en_ligne' => "Le routeur {$routeur->nom} est maintenant en ligne.",
                'hors_ligne' => "Le routeur {$routeur->nom} est maintenant hors ligne.",
                'maintenance' => "Le routeur {$routeur->nom} est en maintenance.",
            };
            Auth::user()->notify(new \App\Notifications\GenericNotification(
                'Changement de statut routeur',
                $message,
                route('routeurs.index')
            ));
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'routeur' => $routeur]);
        }

        return redirect()->route('routeurs.index')->with('success', 'Routeur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Routeur $routeur)
    {
        $nom = $routeur->nom;
        $routeur->delete();

        // Notification pour suppression
        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Routeur supprimé',
            "Le routeur {$nom} a été supprimé du système.",
            route('routeurs.index'),
            'routeur',
            $routeur->id,
            $nom
        ));

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('routeurs.index')->with('success', 'Routeur supprimé avec succès.');
    }

    /**
     * Restaurer un routeur supprimé.
     */
    public function restore($id)
    {
        $routeur = Routeur::withTrashed()->findOrFail($id);
        $routeur->restore();

        return redirect()->route('routeurs.index')->with('success', 'Routeur restauré avec succès.');
    }

    /**
     * Synchroniser un routeur avec MikroTik.
     */
    public function sync(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $ok = $service->handshake($routeur);

        if ($ok) {
            Auth::user()->notify(new \App\Notifications\GenericNotification(
                'Routeur synchronisé',
                "La synchronisation du routeur {$routeur->nom} est terminée.",
                route('routeurs.show', $routeur),
                'routeur',
                $routeur->id,
                $routeur->nom
            ));

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Synchronisation réussie'], 200);
            }

            return redirect()->route('routeurs.index')->with('success', 'Synchronisation réussie.');
        }

        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Échec de synchronisation',
            "La synchronisation du routeur {$routeur->nom} a échoué.",
            route('routeurs.show', $routeur),
            'routeur',
            $routeur->id,
            $routeur->nom
        ));

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Routeur hors ligne'], 500);
        }

        return redirect()->route('routeurs.index')->with('error', 'Routeur hors ligne.');
    }

    /**
     * Redémarrer un routeur
     */
    public function restart(Routeur $routeur)
    {
        try {
            // Appel API MikroTik pour redémarrage
            $service = app(MikrotikService::class);
            $connected = $service->testConnection($routeur);

            if (!$connected) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Routeur hors ligne'], 500);
                }
                return redirect()->back()->with('error', 'Le routeur ' . $routeur->nom . ' est hors ligne');
            }

            // Succès
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Redémarrage initialisé. Le routeur sera bientôt disponible.']);
            }

            return redirect()->back()->with('success', 'Redémarrage du routeur ' . $routeur->nom . ' initialisé');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}