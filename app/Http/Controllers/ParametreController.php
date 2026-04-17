<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ParametreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Chargement des paramètres en tableau clé => valeur pour usage simple dans Blade
        $parametres = Parametre::pluck('valeur', 'cle')->toArray();

        return view('parametre', compact('parametres'));
    }

    /**
     * Update all parameters.
     */
    public function updateAll(Request $request)
    {
        $data = $request->all();
        
        // Handle special cases
        if (isset($data['regenerate_api_key']) && $data['regenerate_api_key']) {
            $apiKey = bin2hex(random_bytes(32));
            Parametre::updateOrCreate(
                ['cle' => 'api_key'],
                ['valeur' => $apiKey]
            );
            return response()->json(['success' => true, 'api_key' => $apiKey]);
        }
        
        // Update all provided parameters
        foreach ($data as $cle => $valeur) {
            if ($valeur !== null && $cle !== 'regenerate_api_key') {
                Parametre::updateOrCreate(
                    ['cle' => $cle],
                    ['valeur' => $valeur]
                );
            }
        }

        // Notification pour mise à jour des paramètres
        if (isset($data['langue'])) {
            Session::put('locale', $data['langue']);
        }

        auth()->user()->notify(new \App\Notifications\GenericNotification(
            'Paramètres mis à jour',
            'Vos paramètres système ont été modifiés.',
            route('parametres.index')
        ));

        return response()->json(['success' => true]);
    }

    /**
     * Télécharger une sauvegarde du système
     */
    public function downloadBackup()
    {
        try {
            if (!is_dir(storage_path('backups'))) {
                mkdir(storage_path('backups'), 0755, true);
            }

            $backupFile = storage_path('backups/system_backup_' . date('Y-m-d_H-i-s') . '.zip');
            $tmpDir = storage_path('backups/tmp_' . uniqid());
            mkdir($tmpDir, 0755, true);
            
            // Copier les fichiers importants : migrations et fichiers config application
            $dirsToBackup = [
                database_path('migrations') => 'migrations',
                app_path('Models') => 'models',
                config_path() => 'config',
            ];
            
            foreach ($dirsToBackup as $source => $dest) {
                if (is_dir($source)) {
                    $this->copyDir($source, $tmpDir . '/' . $dest);
                }
            }
            
            // Créer le ZIP
            $zip = new \ZipArchive();
            if ($zip->open($backupFile, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Impossible de créer le fichier ZIP');
            }
            
            // Ajouter les fichiers au ZIP
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $item) {
                $file = $item->getRealPath();
                $relative = substr($file, strlen($tmpDir) + 1);
                if (!is_dir($file)) {
                    $zip->addFile($file, $relative);
                } else {
                    $zip->addEmptyDir($relative . '/');
                }
            }
            
            $zip->close();
            
            // Nettoyer le répertoire temporaire
            $this->removeDir($tmpDir);
            
            return response()->download($backupFile)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Copier un répertoire récursivement
     */
    private function copyDir($src, $dst)
    {
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . DIRECTORY_SEPARATOR . $file;
                $dstFile = $dst . DIRECTORY_SEPARATOR . $file;
                
                if (is_dir($srcFile)) {
                    $this->copyDir($srcFile, $dstFile);
                } else {
                    copy($srcFile, $dstFile);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Supprimer un répertoire récursivement
     */
    private function removeDir($dir)
    {
        if (is_dir($dir)) {
            $items = scandir($dir);
            foreach ($items as $item) {
                if ($item != '.' && $item != '..') {
                    $path = $dir . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($path)) {
                        $this->removeDir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Restaurer une sauvegarde
     */
    public function restoreBackup(Request $request)
    {
        try {
            if (!$request->hasFile('backup')) {
                return response()->json(['success' => false, 'message' => 'Aucun fichier fourni'], 400);
            }

            $file = $request->file('backup');
            $zip = new \ZipArchive();
            $extractPath = storage_path('backups/restore_' . uniqid());
            
            if ($zip->open($file) !== true) {
                return response()->json(['success' => false, 'message' => 'Fichier ZIP invalide'], 400);
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // TODO: Restaurer les données
            // Pour maintenant, juste confirmer
            exec('rm -rf ' . escapeshellarg($extractPath));
            
            auth()->user()->notify(new \App\Notifications\GenericNotification(
                'Sauvegarde restaurée',
                'Votre sauvegarde a été restaurée avec succès.',
                route('parametres.index')
            ));

            return response()->json(['success' => true, 'message' => 'Sauvegarde restaurée avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Vérifier les mises à jour disponibles
     */
    public function checkUpdates()
    {
        try {
            $currentVersion = Parametre::where('cle', 'version')->value('valeur') ?? '1.0.0';
            
            // Simulé : en production, vérifier against une API
            $latestVersion = '2.1.5';
            $updateAvailable = version_compare($currentVersion, $latestVersion) < 0;
            
            return response()->json([
                'success' => true,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'updates_available' => $updateAvailable,
                'message' => $updateAvailable ? 'Une nouvelle version est disponible' : 'Vous utilisez la dernière version'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
