<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceCategory extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'display_order',
        'active',
        'image_url',
        'color',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'template',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'display_order' => 'integer',
        'parent_id' => 'integer',
        'template' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(ServiceCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ServiceCategory::class, 'parent_id')->orderBy('display_order');
    }

    /**
     * Get all services in this category.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_service_category')->orderBy('name');
    }

    /**
     * The companies that use this service category.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_service_category')
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->whereNull('company_service_category.deleted_at');
    }

    /**
     * Get all descendant categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDescendants()
    {
        $descendants = $this->children;

        foreach ($this->children as $child) {
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Get all services in this category and its descendants.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllServices()
    {
        $categoryIds = $this->getAllDescendants()->pluck('id')->push($this->id);
        return Service::whereIn('category_id', $categoryIds)->get();
    }

    /**
     * Scope a query to only include root categories (no parent).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    
    /**
     * Scope a query to only include template categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTemplate($query)
    {
        return $query->where('template', true);
    }
    
    /**
     * Scope a query to only include non-template categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonTemplate($query)
    {
        return $query->where('template', false);
    }

    /**
     * Get the full path of the category including all ancestors.
     *
     * @param  string  $separator
     * @return string
     */
    public function getFullPath(string $separator = ' > '): string
    {
        $path = [];
        $category = $this;

        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }

        return implode($separator, $path);
    }

    /**
     * Get the category's breadcrumb trail.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBreadcrumb()
    {
        $breadcrumbs = collect([]);
        $category = $this;

        while ($category) {
            $breadcrumbs->prepend([
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);
            $category = $category->parent;
        }

        return $breadcrumbs;
    }
}
