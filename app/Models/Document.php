<?php
// app/Models/Document.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Document extends Model
{
    protected $table = 'documents';
    protected $fillable = ['user_id', 'path', 'name'];
}
