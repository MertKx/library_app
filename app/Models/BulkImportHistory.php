<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulkImportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'total_records',
        'processed_records',
        'error_message',
    ];

    protected $casts = [
        'total_records' => 'integer',
        'processed_records' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED = 'Failed';

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for recent imports
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Get status badge class
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Calculate success rate
    public function getSuccessRate()
    {
        if ($this->total_records === 0 || $this->total_records === null) {
            return 0;
        }
        
        return round(($this->processed_records / $this->total_records) * 100, 2);
    }
}
