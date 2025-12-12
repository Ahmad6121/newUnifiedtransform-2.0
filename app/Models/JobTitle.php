<?php
// app/Models/JobTitle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
