<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\TeleworkingRequest;
use App\Models\AksesLogicRequest;
use Illuminate\Support\Facades\DB;

echo "=== Cleaning up duplicate employees ===\n\n";

try {
    $duplicates = DB::table('employees')
        ->select('nomor_ktp', DB::raw('COUNT(*) as count'))
        ->groupBy('nomor_ktp')
        ->havingRaw('COUNT(*) > 1')
        ->get();

    echo "Found " . $duplicates->count() . " duplicate nomor_ktp values\n\n";

    if ($duplicates->count() === 0) {
        echo "No duplicates found. Database is clean.\n";
        exit(0);
    }

    $totalDeleted = 0;
    $totalUpdated = 0;

    foreach ($duplicates as $duplicate) {
        echo "Processing duplicate KTP: " . $duplicate->nomor_ktp . "\n";

        $employees = Employee::where('nomor_ktp', $duplicate->nomor_ktp)
            ->orderBy('created_at', 'asc')
            ->get();

        $keeper = $employees->first();
        echo "  Keeping employee ID: " . $keeper->id . "\n";

        $employeesArray = $employees->toArray();
        for ($i = 1; $i < count($employeesArray); $i++) {
            $employee = $employeesArray[$i];
            echo "  Deleting employee ID: " . $employee['id'] . "\n";

            $teleworkingUpdated = TeleworkingRequest::where('employee_id', $employee['id'])
                ->update(['employee_id' => $keeper->id]);
            if ($teleworkingUpdated > 0) {
                echo "    Updated " . $teleworkingUpdated . " teleworking request(s)\n";
                $totalUpdated += $teleworkingUpdated;
            }

            $aksesLogicUpdated = AksesLogicRequest::where('employee_id', $employee['id'])
                ->update(['employee_id' => $keeper->id]);
            if ($aksesLogicUpdated > 0) {
                echo "    Updated " . $aksesLogicUpdated . " akses logic request(s)\n";
                $totalUpdated += $aksesLogicUpdated;
            }

            Employee::where('id', $employee['id'])->delete();
            $totalDeleted++;
        }
        echo "\n";
    }

    echo "=== Cleanup complete ===\n";
    echo "Total duplicate employees deleted: " . $totalDeleted . "\n";
    echo "Total requests updated: " . $totalUpdated . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
