<?php

    namespace App\Traits;

    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Log;

    /**
     * Trait ForgetsCacheOnCRUD
     *
     * Automatically invalidates cache keys when a model is created, updated,
     * deleted, force-deleted, or restored.
     *
     * ## Usage
     *
     *   use App\Traits\ForgetsCacheOnCRUD;
     *
     *   class Post extends Model
     *   {
     *       use ForgetsCacheOnCRUD;
     *
     *       protected static function getCacheKeysToForget(): array
     *       {
     *           return [
     *               'posts.all',
     *               fn (self $model) => "posts.{$model->id}",      // dynamic key via closure
     *               fn (self $model) => "users.{$model->user_id}.posts",
     *           ];
     *       }
     *   }
     *
     * ## Bulk operation caveat
     *
     * Eloquent model events do NOT fire for mass updates/deletes:
     *   Post::where('active', false)->delete();  // ← cache NOT invalidated
     *
     * For bulk operations, call Post::forgetConfiguredCaches() manually,
     * or flush relevant keys inside the query itself.
     *
     * ## Cache tags
     *
     * If your cache driver supports tags (Redis, Memcached), prefer tagging
     * over listing individual keys — it is more efficient and avoids stale key lists.
     */
    trait ForgetsCacheOnCRUD
    {
        /**
         * Boot the trait and register cache-busting listeners for model events.
         *
         * NOTE: We intentionally do NOT instantiate `new static()` here.
         * Doing so during the boot cycle triggers bootIfNotBooted() recursively
         * and causes a LogicException in Laravel's model boot process.
         */
        protected static function bootForgetsCacheOnCRUD() : void
        {
            $rawKeys = (array) static::getCacheKeysToForget();

            if ( empty( $rawKeys ) ) {
                if ( app()->environment( 'local' , 'testing' ) ) {
                    Log::warning( sprintf(
                        '[ForgetsCacheOnCRUD] %s uses this trait but getCacheKeysToForget() returns no keys. '
                        . 'Override the method or remove the trait.' ,
                        static::class
                    ) );
                }

                return;
            }

            // Eloquent passes the model instance as the first argument to event listeners.
            // We accept it explicitly here to avoid using $this in a static context.
            $forgetCache = function ($model) use ($rawKeys) {
                foreach ( $rawKeys as $key ) {
                    $resolved = is_callable( $key ) ? $key( $model ) : $key;

                    if ( ! is_string( $resolved ) || $resolved === '' ) {
                        continue;
                    }

                    Cache::forget( $resolved );
                }
            };

            static::created( $forgetCache );
            static::updated( $forgetCache );
            static::deleted( $forgetCache );

            // Conditionally cover soft-delete lifecycle events
            if ( in_array( 'Illuminate\Database\Eloquent\SoftDeletes' , class_uses_recursive( static::class ) , TRUE ) ) {
                static::restored( $forgetCache );
                static::forceDeleted( $forgetCache );
            }
        }

        /**
         * Define cache keys to invalidate on any write operation.
         *
         * Each entry may be:
         *   - a plain string:  'posts.all'
         *   - a Closure:       fn (self $model) => "posts.{$model->id}"
         *                      (receives the model instance as its first argument)
         *
         * This method is STATIC to avoid instantiating the model during boot,
         * which would cause a recursive boot crash in Laravel.
         *
         * Override this method in your model. Returning an empty array disables
         * all cache invalidation and logs a warning in local/testing environments.
         *
         * @return array<int, string|callable>
         */
        protected static function getCacheKeysToForget() : array
        {
            return [];
        }

        /**
         * Manually forget all static cache keys.
         *
         * Use this after bulk operations that bypass Eloquent model events:
         *
         *   Post::where('draft', true)->delete();
         *   Post::forgetConfiguredCaches();
         *
         * Dynamic closure-based keys are skipped here — no instance context is available.
         *
         * @return void
         */
        public static function forgetConfiguredCaches() : void
        {
            foreach ( (array) static::getCacheKeysToForget() as $key ) {
                if ( is_string( $key ) && $key !== '' ) {
                    Cache::forget( $key );
                }
            }
        }
    }