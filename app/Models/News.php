<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class News extends Model
{
    use HasFactory;
    protected $fillable = [
        'time',
        'user_img',
        'title',
        'likes',
        'username',
        'content_txt',
        'content_big_txt',
        'content_url',
        'type',
    ];
}