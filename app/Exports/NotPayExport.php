<?php

namespace App\Exports;

use App\db_credit;
use App\db_summary;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;



class NotPayExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    public function __construct(string $date_start, string $date_end,  int  $user_id)
    {
        $this->date_start = $date_start;
        $this->date_end = $date_end;
        $this->user_id = $user_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data_credit  = db_credit::where('credit.id_agent', $this->user_id)
            ->where('credit.status', 'inprogress')
            ->join('users', 'users.id', '=', 'credit.id_user')
            ->orderBy('credit.created_at', 'asc')
            ->select(
                'credit.id as id_credit',
                'users.id as id_user',
                'users.name',
                'users.last_name'
            )
            ->get();

        $startDate = Carbon::createFromFormat('d/m/Y', $this->date_start);
        $endDate = Carbon::createFromFormat('d/m/Y', $this->date_end);
        $dateRanges = CarbonPeriod::create($startDate, $endDate);
        $daysOfWeek = [];

        foreach ($data_credit as $data) {
            if (db_credit::where('id_user', $data->id_user)->where('id_agent', $this->user_id)->exists()) {

                foreach ($dateRanges->toArray() as $dateRange) {
                    $day = Carbon::parse($dateRange)->Format('l');
                    $daysOfWeek[$day] =  db_summary::where('id_credit', $data->id_credit)
                        ->whereDate('summary.created_at', '=', $dateRange)
                        ->sum('amount');
                }
                $data->summary_day = $daysOfWeek;
            }
        }
        return $data_credit;
    }
    public function map($row): array
    {
        return [
            $row->name . ' ' . $row->last_name,
            $row->summary_day['Monday'] > 0 ?  $row->summary_day['Monday'] . '000' :  '0',
            $row->summary_day['Tuesday'] > 0 ?   $row->summary_day['Tuesday'] . '000' :  '0',
            $row->summary_day['Wednesday'] > 0 ?   $row->summary_day['Wednesday'] . '000' :  '0',
            $row->summary_day['Thursday'] > 0 ?    $row->summary_day['Thursday'] . '000' :  '0',
            $row->summary_day['Friday'] > 0 ?  $row->summary_day['Friday'] . '000' :  '0',
            $row->summary_day['Saturday'] > 0 ?   $row->summary_day['Saturday'] . '000' :  '0',
            $row->summary_day['Sunday'] > 0 ?   $row->summary_day['Sunday'] . '000' :  '0',
        ];
    }
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Cliente',
            'Lunes',
            'Martes',
            'Miercoles',
            'Jueves',
            'Viernes',
            'Sabado',
            'Domingo',
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
        ];
    }

    public function registerEvents(): array
    {

        $styleArray = [
            'font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']
        ];
        $styleArray2 = [
            'fill' => ['fillType' => 'solid', 'color' => array('rgb' => '18B4FF')],

        ];
        $styleArray3 = [
            'fill' => ['fillType' => 'solid', 'color' => array('rgb' => 'EBFA1B')],
        ];
        $styleArray4 = [
            'alignment' => ['horizontal' => 'center']
        ];
        $styleArray5 = [
            'font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']
        ];
        $style_not_pay = [
            'fill' => ['fillType' => 'solid', 'color' => array('rgb' => 'eb596e')],
        ];
        $style_pay = [
            'fill' => ['fillType' => 'solid', 'color' => array('rgb' => '9ede73')],
        ];

        return [
            AfterSheet::class    => function (AfterSheet $event) use (
                $styleArray,
                $styleArray2,
                $styleArray3,
                $styleArray4,
                $styleArray5,
                $style_not_pay,
                $style_pay
            ) {
                $to = $event->sheet->getDelegate()->getHighestRowAndColumn();
                $rows = $event->sheet->getDelegate()->toArray();
                $cellRange = 'A1:H1';
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getStyle('A1')->ApplyFromArray($styleArray2);
                $event->sheet->getStyle('A')->ApplyFromArray($styleArray5);
                $event->sheet->getStyle('B1:H1')->ApplyFromArray($styleArray3);
                $event->sheet->getStyle('A:H')->ApplyFromArray($styleArray4);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setName('Arial');
                $event->sheet->getStyle('A1')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $event->sheet->getStyle('A1:' . $to['column'] . $to['row'],)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);


                $column_b = 0;
                $column_c = 0;
                $column_d = 0;
                $column_e = 0;
                $column_f = 0;
                $column_g = 0;
                $column_h = 0;

                foreach ($rows as $key => $row) {
                    if (is_numeric($row[1])) {
                        $column_b = $column_b + $row[1];
                        $k = $key + 1;
                        if ($row[1] > 0) {
                            $event->sheet->getStyle('B' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('B' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                    if (is_numeric($row[2])) {
                        $column_c = $column_c + $row[2];
                        $k = $key + 1;
                        if ($row[2] > 0) {
                            $event->sheet->getStyle('C' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('C' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                    if (is_numeric($row[3])) {
                        $column_d = $column_d + $row[3];
                        $k = $key + 1;
                        if ($row[3] > 0) {
                            $event->sheet->getStyle('D' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('D' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                    if (is_numeric($row[4])) {
                        $column_e = $column_e + $row[4];
                        $k = $key + 1;
                        if ($row[4] > 0) {
                            $event->sheet->getStyle('E' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('E' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                    if (is_numeric($row[5])) {
                        $column_f = $column_f + $row[5];
                        $k = $key + 1;
                        if ($row[5] > 0) {
                            $event->sheet->getStyle('F' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('F' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                    if (is_numeric($row[6])) {
                        $column_g = $column_g + $row[6];
                        $k = $key + 1;
                        if ($row[6] > 0) {
                            $event->sheet->getStyle('G' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('G' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                    if (is_numeric($row[7])) {
                        $column_h = $column_h + $row[7];
                        $k = $key + 1;
                        if ($row[7] > 0) {
                            $event->sheet->getStyle('H' . $k)->ApplyFromArray($style_pay);
                        } else {
                            $event->sheet->getStyle('H' . $k)->ApplyFromArray($style_not_pay);
                        }
                    }
                }
                $event->sheet->appendRows(array(
                    array(
                        'Total cobrado',
                        "$column_b",
                        "$column_c",
                        "$column_d",
                        "$column_e",
                        "$column_f",
                        "$column_g",
                        "$column_h",
                    ),
                ), $event);

                $total_rows = count($rows) + 1;
                $range = 'A' . $total_rows . ':' . 'H' . $total_rows;
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'fill' => ['fillType' => 'solid', 'color' => array('rgb' => 'd8e3e7')],
                ]);
            },
        ];
    }
}
