<?php

namespace Dewsign\NovaBlog\Models;

use ScoutElastic\Searchable;
use Maxfactor\Support\Webpage\Model;
use Illuminate\Support\Facades\Route;
use Maxfactor\Support\Webpage\Traits\HasSlug;
use Maxfactor\Support\Model\Traits\CanBeFeatured;
use Maxfactor\Support\Model\Traits\HasActiveState;
use Maxfactor\Support\Webpage\Traits\HasMetaAttributes;
use Maxfactor\Support\Webpage\Traits\MustHaveCanonical;
use Dewsign\NovaRepeaterBlocks\Traits\HasRepeaterBlocks;
use Dewsign\NovaBlog\IndexConfigurators\BlogIndexConfigurator;

class Article extends Model
{
    use HasSlug;
    use Searchable;
    use CanBeFeatured;
    use HasActiveState;
    use HasMetaAttributes;
    use HasRepeaterBlocks;
    use MustHaveCanonical;

    protected $indexConfigurator = BlogIndexConfigurator::class;

    // Mapping for a model fields.
    protected $mapping = [
        'properties' => [
            'text' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ]
                ]
            ],
        ]
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $this->repeaters;
        $searchable = $this->toArray();

        $searchable = array_except($searchable, [
            'active',
            'browser_title',
            'h1',
            'meta_description',
            'nav_title',
            'canonical',
        ]);

        return $searchable;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'blog_articles';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'published_date',
    ];

    /**
     * Get an article's categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_article_blog_category')->ordered();
    }

    /**
     * Return the first category to be used as the primary category (e.g. for canonical url)
     *
     * @return Collection
     */
    public function getPrimaryCategoryAttribute()
    {
        return $this->categories->first();
    }

    public function getFeaturedImageLargeAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return cloudinary_image($this->image, [
            "width" => 800,
            "height" => 450,
            "crop" => "fill",
            "gravity" => "auto",
            "fetch_format" => "auto",
        ]);
    }

    /**
     * Add required items to the breadcrumb seed
     *
     * @return array
     */
    public function seeds()
    {
        $category = Category::whereSlug(Route::input('category'))->first();

        return array_merge(parent::seeds(), [
            [
                'name' => __('Blog'),
                'url' => route('blog.index'),
            ],
            [
                'name' => array_get($category ?? $this->primaryCategory, 'navTitle'),
                'url' => route('blog.list', [$category ?? $this->primaryCategory]),
            ],
            [
                'name' => $this->navTitle,
                'url' => route('blog.show', [$category ?? $this->primaryCategory, $this]),
            ],
        ]);
    }

    public function baseCanonical()
    {
        return route('blog.show', [$this->primaryCategory, $this]);
    }
}
