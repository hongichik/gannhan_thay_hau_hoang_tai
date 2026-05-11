<?php

namespace Hongdev\MasterAdmin\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controller;

class MigrationController extends Controller
{
    /**
     * Display a listing of migrations
     */
    public function index(Request $request)
    {
        $migrationPath = database_path('migrations');
        $migrations = [];
        
        if (File::exists($migrationPath)) {
            $files = File::files($migrationPath);
            
            foreach ($files as $file) {
                $migrations[] = [
                    'filename' => $file->getFilename(),
                    'name' => $this->getMigrationName($file->getFilename()),
                    'created_at' => date('Y-m-d H:i:s', $file->getCTime()),
                    'size' => $file->getSize(),
                    'path' => $file->getPathname()
                ];
            }
            
            // Sort by filename (which includes timestamp)
            usort($migrations, function ($a, $b) {
                return strcmp($a['filename'], $b['filename']);
            });
        }
        
        if ($request->ajax()) {
            return response()->json([
                'data' => $migrations,
                'total' => count($migrations)
            ]);
        }
        
        return view('masteradmin::settings.migrations.index', compact('migrations'));
    }
    
    /**
     * Search migrations
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $migrationPath = database_path('migrations');
        $migrations = [];
        
        if (File::exists($migrationPath)) {
            $files = File::files($migrationPath);
            
            foreach ($files as $file) {
                $filename = $file->getFilename();
                if (empty($query) || stripos($filename, $query) !== false) {
                    $migrations[] = [
                        'filename' => $filename,
                        'name' => $this->getMigrationName($filename),
                        'created_at' => date('Y-m-d H:i:s', $file->getCTime()),
                        'size' => $file->getSize(),
                        'path' => $file->getPathname()
                    ];
                }
            }
        }
        
        return response()->json([
            'data' => $migrations,
            'total' => count($migrations)
        ]);
    }
    
    /**
     * Get migration details
     */
    public function details($filename)
    {
        $migrationPath = database_path('migrations/' . $filename);
        
        if (!File::exists($migrationPath)) {
            return response()->json(['error' => 'Migration file not found'], 404);
        }
        
        $content = File::get($migrationPath);
        $stats = File::stat($migrationPath);
        
        return response()->json([
            'filename' => $filename,
            'name' => $this->getMigrationName($filename),
            'content' => $content,
            'size' => $stats['size'],
            'created_at' => date('Y-m-d H:i:s', $stats['ctime']),
            'modified_at' => date('Y-m-d H:i:s', $stats['mtime']),
        ]);
    }
    
    /**
     * Extract migration name from filename
     */
    private function getMigrationName($filename)
    {
        // Remove timestamp and .php extension
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename);
        $name = str_replace('.php', '', $name);
        
        // Convert to readable format
        return ucwords(str_replace('_', ' ', $name));
    }
}