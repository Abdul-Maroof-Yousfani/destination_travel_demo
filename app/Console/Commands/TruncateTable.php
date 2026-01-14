<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate specific database tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // List of tables to truncate
        $tables = [
            'bookings',
            'booking_items',
            'booking_request_bodies',
            'cancel_responses',
            'clients',
            'error_logs',
            'flights',
            'passengers',
            'payments',
            'penalties',
            'segments',
            'tickets',
            'logs',
            // Add more table names as needed
        ];
        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->info("Truncated: $table");
        }

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->info('Selected tables have been truncated successfully.');
    }
}
