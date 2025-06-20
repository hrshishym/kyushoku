<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'main_dish',
        'side_dish',
        'soup',
        'rice',
        'drink',
        'dessert',
        'other',
        'pdf_path'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getMenuForDate($date)
    {
        return self::where('user_id', auth()->id())
                   ->where('date', $date)
                   ->first();
    }

    public static function getTodaysMenu()
    {
        return self::getMenuForDate(Carbon::today());
    }

    public static function getTomorrowsMenu()
    {
        return self::getMenuForDate(Carbon::tomorrow());
    }

    public static function getMenusForMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        return self::where('user_id', auth()->id())
                   ->whereBetween('date', [$startDate, $endDate])
                   ->orderBy('date')
                   ->get();
    }
}
