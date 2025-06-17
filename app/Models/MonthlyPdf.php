<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonthlyPdf extends Model
{
   use HasFactory;

   protected $fillable = [
       'year',
       'month',
       'pdf_path',
       'original_filename',
       'total_days_parsed',
       'parsing_status',
       'parsing_errors'
   ];

   protected $casts = [
       'parsing_status' => 'array',
   ];

   public function menus()
   {
       $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
       $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
       
       return Menu::whereBetween('date', [$startDate, $endDate])
                  ->orderBy('date');
   }

   public function getFormattedMonthAttribute()
   {
       return Carbon::create($this->year, $this->month, 1)->format('Y年m月');
   }

   public static function findByYearMonth(int $year, int $month)
   {
       return self::where('year', $year)->where('month', $month)->first();
   }
}