<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'user_id',
        'name',
        'slug',
        'description',
        'price_per_day',
        'suggested_price',
        'stock',
        'size',
        'color',
        'material',
        'condition',
        'status',
        'is_featured',
        'dp_percentage',
        'weight',
        'length',
        'width',
        'height',
        'views_count',
        'rental_count',
        'rating_average',
        'rating_count',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'decimal:2',
            'suggested_price' => 'decimal:2',
            'dp_percentage' => 'decimal:2',
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'is_featured' => 'boolean',
            'rating_average' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . uniqid();
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name) . '-' . uniqid();
            }
        });
    }

    public function consignor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(ProductPhoto::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function incrementViewsCount(): void
    {
        $this->increment('views_count');
    }

    public function updateRating(): void
    {
        $stats = $this->reviews()->selectRaw('AVG(rating) as avg, COUNT(*) as count')->first();
        $this->update([
            'rating_average' => $stats->avg ?? 0,
            'rating_count' => $stats->count ?? 0,
        ]);
    }

    public function getBookedQuantityForDateRange(string $startDate, string $endDate): int
    {
        return (int) OrderItem::where('product_id', $this->id)
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', [
                'cancelled', 'completed', 'returned',
            ]))
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(fn ($q) => $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate));
            })
            ->sum('quantity');
    }

    public function getAvailableQuantityForDateRange(string $startDate, string $endDate): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return max(0, $this->stock - $this->getBookedQuantityForDateRange($startDate, $endDate));
    }

    public function isAvailableForDateRange(string $startDate, string $endDate, int $quantity = 1): bool
    {
        return $this->getAvailableQuantityForDateRange($startDate, $endDate) >= $quantity;
    }
}
