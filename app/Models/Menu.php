<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public static function getMenuForDate($date)
    {
        return self::where('date', $date)->first();
    }

    public static function getTodaysMenu()
    {
        return self::getMenuForDate(Carbon::today());
    }

    public static function getTomorrowsMenu()
    {
        return self::getMenuForDate(Carbon::tomorrow());
    }
}