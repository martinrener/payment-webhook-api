<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;


class ExportService
{
    public function exportPayments(array $data): StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Event', 'Amount', 'Currency', 'User', 'Date']); // headers
            foreach ($data as $row) {
                fputcsv($output, [$row['payment_id'], $row['event'], $row['amount'], $row['currency'], $row['user_id'], $row['created_at']]);
            }
            fclose($output);
        }, 'payments.csv');    
    }
}