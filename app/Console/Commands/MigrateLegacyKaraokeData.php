<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class MigrateLegacyKaraokeData extends Command
{
    protected $signature = 'legacy:migrate-karaoke
        {--legacy-sql=C:\Users\ASUS\Downloads\karaoke.sql : Path to the old karaoke SQL dump}
        {--dry-run : Parse and report without writing data}
        {--commit : Clear target tables and write migrated data}
        {--report= : Optional report path}';

    protected $description = 'Migrate the legacy karaoke SQL dump into the new schema, archiving every original row.';

    private const LANGUAGE_MAP = [
        'vietnamese' => 1,
        'english' => 2,
        '' => 1,
    ];

    private array $legacy = [];
    private array $legacyUserMap = [];
    private array $legacyUserGroupMap = [];
    private array $legacyCustomerGroupMap = [];
    private array $productAttributes = [];
    private array $usedRouterCanonicals = [];
    private array $pivotKeys = [];
    private array $tableColumns = [];
    private bool $commit = false;
    private array $report = [
        'mode' => 'dry-run',
        'legacy_sql' => null,
        'input_rows' => [],
        'cleared_tables' => [],
        'seeded_rows' => [],
        'archived_legacy_tables' => [],
        'duplicate_canonicals' => [],
        'missing_foreign_keys' => [],
        'failed_rows' => [],
        'warnings' => [],
    ];

    private array $mappedLegacyTables = [
        'articles',
        'articles_catalogues',
        'attributes',
        'attributes_catalogues',
        'attributes_relationship',
        'catalogues_relationship',
        'contacts',
        'customers',
        'customers_groups',
        'navigations_menus',
        'navigations_menus_items',
        'navigations_positions',
        'payments',
        'payments_items',
        'products',
        'products_catalogues',
        'province',
        'slides',
        'slides_groups',
        'systems',
        'users',
        'users_groups',
    ];

    private array $clearTables = [
        'legacy_import_records',
        'routers',
        'school_post',
        'construction_product',
        'combo_products',
        'promotion_gifts',
        'promotion_rules',
        'promotion_product_variant',
        'product_variant_attribute',
        'product_variant_language',
        'product_variants',
        'order_paymentable',
        'order_product',
        'customer_point_history',
        'reviews',
        'contacts',
        'orders',
        'wards',
        'districts',
        'provinces',
        'customers',
        'customer_catalogues',
        'menu_language',
        'menus',
        'menu_catalogues',
        'post_catalogue_post',
        'post_language',
        'posts',
        'post_catalogue_language',
        'post_catalogues',
        'product_catalogue_product',
        'product_language',
        'products',
        'product_catalogue_language',
        'product_catalogues',
        'attribute_catalogue_attribute',
        'attribute_language',
        'attributes',
        'attribute_catalogue_language',
        'attribute_catalogues',
        'slides',
        'systems',
    ];

    public function handle(): int
    {
        $legacySql = (string) $this->option('legacy-sql');
        $commit = (bool) $this->option('commit');
        $dryRun = (bool) $this->option('dry-run') || !$commit;
        $this->commit = $commit;

        if ($commit && (bool) $this->option('dry-run')) {
            $this->error('Use either --dry-run or --commit, not both.');
            return self::FAILURE;
        }

        if (!is_file($legacySql)) {
            $this->error("Legacy SQL file not found: {$legacySql}");
            return self::FAILURE;
        }

        $this->report['mode'] = $commit ? 'commit' : 'dry-run';
        $this->report['legacy_sql'] = $legacySql;

        if ($commit && !Schema::hasTable('legacy_import_records')) {
            $this->error('Table legacy_import_records does not exist. Run php artisan migrate first.');
            return self::FAILURE;
        }

        try {
            if ($commit) {
                $this->clearTargetTables();
                $this->ensureBaseUser();
                $this->ensureLanguages();
            }

            $this->readLegacySql($legacySql, $commit);
            $this->migrateMappedTables($commit);
            $this->writeReport();
        } catch (Throwable $exception) {
            $this->report['warnings'][] = $exception->getMessage();
            $this->writeReport();
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->printSummary();
        return self::SUCCESS;
    }

    private function clearTargetTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->clearTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->truncate();
            $this->report['cleared_tables'][] = $table;
        }

        Schema::enableForeignKeyConstraints();
    }

    private function readLegacySql(string $legacySql, bool $commit): void
    {
        $handle = fopen($legacySql, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open legacy SQL file: {$legacySql}");
        }

        $statement = '';
        $collecting = false;

        while (($line = fgets($handle)) !== false) {
            if (!$collecting && !str_starts_with(ltrim($line), 'INSERT INTO `')) {
                if (preg_match('/^CREATE TABLE `([^`]+)`/', ltrim($line), $matches) === 1) {
                    $this->increment($this->report['input_rows'], $matches[1], 0);
                }

                continue;
            }

            $collecting = true;
            $statement .= $line;

            if (preg_match('/;\s*$/', $line) !== 1) {
                continue;
            }

            [$table, $columns, $rows] = $this->parseInsertStatement($statement);
            $this->increment($this->report['input_rows'], $table, count($rows));

            if (in_array($table, $this->mappedLegacyTables, true)) {
                foreach ($rows as $row) {
                    $this->legacy[$table][] = array_combine($columns, $row);
                }
            } else {
                $assocRows = [];
                foreach ($rows as $row) {
                    $assocRows[] = array_combine($columns, $row);
                }
                $this->archiveRows($table, $assocRows, $commit, null, null, 'No direct target table in the new schema.');
            }

            $statement = '';
            $collecting = false;
        }

        fclose($handle);
    }

    private function parseInsertStatement(string $statement): array
    {
        if (preg_match('/INSERT INTO `([^`]+)`\s+\((.*?)\)\s+VALUES\s*(.*);\s*$/s', $statement, $matches) !== 1) {
            throw new \RuntimeException('Unable to parse INSERT statement.');
        }

        $table = $matches[1];
        preg_match_all('/`([^`]+)`/', $matches[2], $columnMatches);

        return [$table, $columnMatches[1], $this->parseValues($matches[3])];
    }

    private function parseValues(string $values): array
    {
        $rows = [];
        $row = [];
        $token = '';
        $inString = false;
        $escaped = false;
        $depth = 0;

        $length = strlen($values);
        for ($i = 0; $i < $length; $i++) {
            $char = $values[$i];

            if ($inString) {
                $token .= $char;
                if ($escaped) {
                    $escaped = false;
                    continue;
                }
                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }
                if ($char === "'") {
                    $inString = false;
                }
                continue;
            }

            if ($char === "'") {
                $inString = true;
                $token .= $char;
                continue;
            }

            if ($char === '(') {
                if ($depth === 0) {
                    $row = [];
                    $token = '';
                } else {
                    $token .= $char;
                }
                $depth++;
                continue;
            }

            if ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    $row[] = $this->normalizeSqlToken($token);
                    $rows[] = $row;
                    $token = '';
                } else {
                    $token .= $char;
                }
                continue;
            }

            if ($char === ',' && $depth === 1) {
                $row[] = $this->normalizeSqlToken($token);
                $token = '';
                continue;
            }

            if ($depth > 0) {
                $token .= $char;
            }
        }

        return $rows;
    }

    private function normalizeSqlToken(string $token): mixed
    {
        $token = trim($token);
        if ($token === '' || strtoupper($token) === 'NULL') {
            return null;
        }

        if (strlen($token) >= 2 && $token[0] === "'" && substr($token, -1) === "'") {
            return stripcslashes(substr($token, 1, -1));
        }

        return $token;
    }

    private function migrateMappedTables(bool $commit): void
    {
        $this->buildProductAttributeMap();
        $this->migrateUserGroups($commit);
        $this->migrateUsers($commit);
        $this->migrateCustomerGroups($commit);
        $this->migrateCustomers($commit);
        $this->migrateAttributeCatalogues($commit);
        $this->migrateAttributes($commit);
        $this->migratePostCatalogues($commit);
        $this->migratePosts($commit);
        $this->migrateProductCatalogues($commit);
        $this->migrateProducts($commit);
        $this->migrateCatalogueRelationships($commit);
        $this->migrateProvinces($commit);
        $this->migrateContacts($commit);
        $this->migrateOrders($commit);
        $this->migrateMenus($commit);
        $this->migrateSlides($commit);
        $this->migrateSystems($commit);
    }

    private function buildProductAttributeMap(): void
    {
        $attributeCatalogueById = [];
        foreach ($this->legacy['attributes'] ?? [] as $attribute) {
            $attributeCatalogueById[(int) $attribute['id']] = (int) ($attribute['cataloguesid'] ?? 0);
        }

        foreach ($this->legacy['attributes_relationship'] ?? [] as $relation) {
            $productId = (int) ($relation['productsid'] ?? 0);
            $attributeId = (int) ($relation['attrid'] ?? 0);
            $catalogueId = $attributeCatalogueById[$attributeId] ?? 0;
            $this->archiveMappedRow('attributes_relationship', $relation, 'products', $productId, 'Legacy product attribute relationship.');

            if ($productId <= 0 || $attributeId <= 0 || $catalogueId <= 0) {
                continue;
            }

            $this->productAttributes[$productId][$catalogueId][] = $attributeId;
        }

        foreach ($this->productAttributes as $productId => $catalogues) {
            foreach ($catalogues as $catalogueId => $attributes) {
                $this->productAttributes[$productId][$catalogueId] = array_values(array_unique($attributes));
            }
        }
    }

    private function migrateUserGroups(bool $commit): void
    {
        foreach ($this->legacy['users_groups'] ?? [] as $row) {
            $oldId = (int) $row['id'];
            $name = $this->blankToNull($row['title'] ?? null) ?? "Legacy user group {$oldId}";
            $this->archiveMappedRow('users_groups', $row, 'user_catalogues', $oldId);

            if (!$commit) {
                $this->increment($this->report['seeded_rows'], 'user_catalogues');
                $this->legacyUserGroupMap[$oldId] = $oldId;
                continue;
            }

            $existing = DB::table('user_catalogues')->where('name', $name)->first();
            if ($existing) {
                $this->legacyUserGroupMap[$oldId] = (int) $existing->id;
                continue;
            }

            $newId = DB::table('user_catalogues')->insertGetId([
                'name' => $name,
                'description' => $this->blankToNull($row['description'] ?? null),
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
            ]);

            $this->legacyUserGroupMap[$oldId] = $newId;
            $this->increment($this->report['seeded_rows'], 'user_catalogues');
        }
    }

    private function migrateUsers(bool $commit): void
    {
        foreach ($this->legacy['users'] ?? [] as $row) {
            $oldId = (int) $row['id'];
            $email = $this->blankToNull($row['email'] ?? null) ?? "legacy-user-{$oldId}@legacy.local";
            $this->archiveMappedRow('users', $row, 'users', $oldId);

            if (!$commit) {
                $this->increment($this->report['seeded_rows'], 'users');
                $this->legacyUserMap[$oldId] = $oldId;
                continue;
            }

            $existing = DB::table('users')->where('email', $email)->first();
            if ($existing) {
                $this->legacyUserMap[$oldId] = (int) $existing->id;
                continue;
            }

            $newId = DB::table('users')->insertGetId([
                'name' => $this->blankToNull($row['fullname'] ?? null) ?? "Legacy User {$oldId}",
                'phone' => $this->blankToNull($row['phone'] ?? null),
                'province_id' => null,
                'district_id' => null,
                'ward_id' => null,
                'address' => $this->blankToNull($row['address'] ?? null),
                'birthday' => null,
                'image' => '',
                'description' => $this->blankToNull($row['description'] ?? null),
                'user_agent' => $this->blankToNull($row['user_agent'] ?? null),
                'ip' => $this->blankToNull($row['remote_addr'] ?? null),
                'email' => $email,
                'email_verified_at' => null,
                'password' => $this->validPassword($row['password'] ?? null) ? $row['password'] : Hash::make('legacy-import-password'),
                'remember_token' => null,
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
                'user_catalogue_id' => $this->legacyUserGroupMap[(int) ($row['groupsid'] ?? 0)] ?? 2,
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'publish' => $this->mapPublish($row['publish'] ?? null),
            ]);

            if (!$this->validPassword($row['password'] ?? null)) {
                $this->report['warnings'][] = "Legacy user {$oldId} password was replaced with a reset placeholder.";
            }

            $this->legacyUserMap[$oldId] = $newId;
            $this->increment($this->report['seeded_rows'], 'users');
        }
    }

    private function migrateCustomerGroups(bool $commit): void
    {
        foreach ($this->legacy['customers_groups'] ?? [] as $row) {
            $id = (int) $row['id'];
            $this->archiveMappedRow('customers_groups', $row, 'customer_catalogues', $id);
            $payload = [
                'id' => $id,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Legacy customer group {$id}",
                'description' => $this->blankToNull($row['description'] ?? null),
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
            ];

            if (Schema::hasColumn('customer_catalogues', 'point_percent')) {
                $payload['point_percent'] = 0;
            }

            $this->store('customer_catalogues', $payload, $commit, 'customers_groups', $id);
            $this->legacyCustomerGroupMap[$id] = $id;
        }
    }

    private function migrateCustomers(bool $commit): void
    {
        $this->ensureSource($commit);

        foreach ($this->legacy['customers'] ?? [] as $row) {
            $id = (int) $row['id'];
            $this->archiveMappedRow('customers', $row, 'customers', $id);
            $payload = [
                'id' => $id,
                'customer_catalogue_id' => $this->legacyCustomerGroupMap[(int) ($row['groupsid'] ?? 0)] ?? 1,
                'google_id' => null,
                'facebook_id' => null,
                'code' => $this->blankToNull($row['code'] ?? null) ?? "LEGACY-C{$id}",
                'name' => $this->blankToNull($row['fullname'] ?? null) ?? "Legacy Customer {$id}",
                'phone' => $this->blankToNull($row['phone'] ?? null),
                'province_id' => $this->blankToNull($row['cityid'] ?? null),
                'district_id' => $this->blankToNull($row['districtid'] ?? null),
                'ward_id' => null,
                'address' => $this->blankToNull($row['address'] ?? null),
                'point' => 0,
                'birthday' => null,
                'image' => '',
                'description' => $this->blankToNull($row['description'] ?? null),
                'user_agent' => $this->blankToNull($row['user_agent'] ?? null),
                'ip' => $this->blankToNull($row['remote_addr'] ?? null),
                'email' => $this->blankToNull($row['email'] ?? null),
                'email_verified_at' => null,
                'password' => $this->validPassword($row['password'] ?? null) ? $row['password'] : Hash::make('legacy-import-password'),
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'remember_token' => null,
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
                'source_id' => 3,
            ];

            if (Schema::hasColumn('customers', 'viettelpost_email')) {
                $payload['viettelpost_email'] = null;
                $payload['viettelpost_password'] = null;
            }

            $this->store('customers', $payload, $commit, 'customers', $id);
        }
    }

    private function migrateAttributeCatalogues(bool $commit): void
    {
        foreach ($this->legacy['attributes_catalogues'] ?? [] as $row) {
            $id = (int) $row['id'];
            $timestamps = $this->timestamps($row);
            $this->archiveMappedRow('attributes_catalogues', $row, 'attribute_catalogues', $id);
            $this->store('attribute_catalogues', [
                'id' => $id,
                'parent_id' => 0,
                'lft' => max(1, $id * 2 - 1),
                'rgt' => max(2, $id * 2),
                'level' => 0,
                'image' => '',
                'icon' => null,
                'album' => '',
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'follow' => 2,
                'order' => (int) ($row['order'] ?? 0),
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                ...$timestamps,
            ], $commit, 'attributes_catalogues', $id);

            $this->store('attribute_catalogue_language', [
                'attribute_catalogue_id' => $id,
                'language_id' => 1,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Attribute catalogue {$id}",
                'description' => $this->blankToNull($row['description'] ?? null) ?? '',
                'content' => '',
                'meta_title' => '',
                'meta_keyword' => '',
                'meta_description' => '',
                'canonical' => $this->slug($row['keyword'] ?? $row['title'] ?? "attribute-catalogue-{$id}"),
                ...$timestamps,
            ], $commit, 'attributes_catalogues', $id);
        }
    }

    private function migrateAttributes(bool $commit): void
    {
        foreach ($this->legacy['attributes'] ?? [] as $row) {
            $id = (int) $row['id'];
            $catalogueId = (int) ($row['cataloguesid'] ?? 0);
            $timestamps = $this->timestamps($row);
            $this->archiveMappedRow('attributes', $row, 'attributes', $id);

            if ($catalogueId <= 0) {
                $this->missingFk('attributes', $id, 'attribute_catalogues', $catalogueId);
                continue;
            }

            $this->store('attributes', [
                'id' => $id,
                'attribute_catalogue_id' => $catalogueId,
                'image' => null,
                'icon' => null,
                'album' => '',
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'follow' => 2,
                'order' => (int) ($row['order'] ?? 0),
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                ...$timestamps,
            ], $commit, 'attributes', $id);

            $this->store('attribute_language', [
                'attribute_id' => $id,
                'language_id' => 1,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Attribute {$id}",
                'description' => $this->blankToNull($row['description'] ?? null) ?? '',
                'content' => '',
                'meta_title' => '',
                'meta_keyword' => '',
                'meta_description' => '',
                'canonical' => $this->slug($row['canonical'] ?? $row['slug'] ?? $row['title'] ?? "attribute-{$id}"),
                ...$timestamps,
            ], $commit, 'attributes', $id);

            $this->store('attribute_catalogue_attribute', [
                'attribute_catalogue_id' => $catalogueId,
                'attribute_id' => $id,
            ], $commit, 'attributes', $id);
        }
    }

    private function migratePostCatalogues(bool $commit): void
    {
        foreach ($this->legacy['articles_catalogues'] ?? [] as $row) {
            $id = (int) $row['id'];
            $languageId = $this->languageId($row['alanguage'] ?? null);
            $timestamps = $this->timestamps($row);
            $canonical = $this->uniqueCanonical('PostCatalogueController', $languageId, $row['canonical'] ?? $row['slug'] ?? $row['title'] ?? "post-catalogue-{$id}", $id);
            $this->archiveMappedRow('articles_catalogues', $row, 'post_catalogues', $id);

            $this->store('post_catalogues', [
                'id' => $id,
                'parent_id' => (int) ($row['parentid'] ?? 0),
                'lft' => (int) ($row['lft'] ?? max(1, $id * 2 - 1)),
                'rgt' => (int) ($row['rgt'] ?? max(2, $id * 2)),
                'level' => (int) ($row['level'] ?? 0),
                'image' => $this->blankToNull($row['images'] ?? null),
                'icon' => null,
                'album' => '',
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'order' => (int) ($row['order'] ?? 0),
                'short_name' => $this->blankToNull($row['title'] ?? null),
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'follow' => 2,
                ...$timestamps,
            ], $commit, 'articles_catalogues', $id);

            $this->store('post_catalogue_language', [
                'post_catalogue_id' => $id,
                'language_id' => $languageId,
                'url' => $canonical,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Post catalogue {$id}",
                'description' => $this->blankToNull($row['description'] ?? null) ?? '',
                'content' => '',
                'meta_title' => $this->blankToNull($row['meta_title'] ?? null) ?? '',
                'meta_keyword' => $this->blankToNull($row['meta_keyword'] ?? null) ?? '',
                'meta_description' => $this->blankToNull($row['meta_description'] ?? null) ?? '',
                'canonical' => $canonical,
                ...$timestamps,
            ], $commit, 'articles_catalogues', $id);

            $this->storeRouter('routers', $canonical, $id, $languageId, 'PostCatalogueController', $commit, 'articles_catalogues', $id, $timestamps);
        }
    }

    private function migratePosts(bool $commit): void
    {
        foreach ($this->legacy['articles'] ?? [] as $row) {
            $id = (int) $row['id'];
            $languageId = $this->languageId($row['alanguage'] ?? null);
            $catalogueId = (int) ($row['cataloguesid'] ?? 0);
            $timestamps = $this->timestamps($row);
            $canonical = $this->uniqueCanonical('PostController', $languageId, $row['canonical'] ?? $row['slug'] ?? $row['title'] ?? "post-{$id}", $id);
            $this->archiveMappedRow('articles', $row, 'posts', $id);

            $this->store('posts', [
                'id' => $id,
                'post_catalogue_id' => $catalogueId,
                'image' => $this->blankToNull($row['images'] ?? null),
                'icon' => null,
                'album' => '',
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'order' => (int) ($row['order'] ?? 0),
                'follow' => 2,
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'video' => '',
                'template' => null,
                'viewed' => (int) ($row['viewed'] ?? 0),
                'status_menu' => 1,
                'short_name' => $this->blankToNull($row['title'] ?? null),
                'logo' => null,
                'extra' => null,
                'rate' => null,
                'comments' => null,
                'post_type' => 'post',
                'recommend' => '1',
                'released_at' => null,
                'files' => '',
                ...$timestamps,
            ], $commit, 'articles', $id);

            $this->store('post_language', [
                'post_id' => $id,
                'language_id' => $languageId,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Post {$id}",
                'description' => $this->blankToNull($row['description'] ?? null) ?? '',
                'content' => $this->blankToNull($row['content'] ?? null) ?? '',
                'meta_title' => $this->blankToNull($row['meta_title'] ?? null) ?? '',
                'meta_keyword' => $this->blankToNull($row['meta_keyword'] ?? null) ?? '',
                'meta_description' => $this->blankToNull($row['meta_description'] ?? null) ?? '',
                'canonical' => $canonical,
                ...$timestamps,
            ], $commit, 'articles', $id);

            $this->attachCatalogues('post_catalogue_post', 'post_catalogue_id', 'post_id', $row['catalogues'] ?? null, $catalogueId, $id, $commit, 'articles');
            $this->storeRouter('routers', $canonical, $id, $languageId, 'PostController', $commit, 'articles', $id, $timestamps);
        }
    }

    private function migrateProductCatalogues(bool $commit): void
    {
        foreach ($this->legacy['products_catalogues'] ?? [] as $row) {
            $id = (int) $row['id'];
            $languageId = $this->languageId($row['alanguage'] ?? null);
            $timestamps = $this->timestamps($row);
            $canonical = $this->uniqueCanonical('ProductCatalogueController', $languageId, $row['canonical'] ?? $row['slug'] ?? $row['title'] ?? "product-catalogue-{$id}", $id);
            $this->archiveMappedRow('products_catalogues', $row, 'product_catalogues', $id);

            $this->store('product_catalogues', [
                'id' => $id,
                'parent_id' => (int) ($row['parentid'] ?? 0),
                'lft' => (int) ($row['lft'] ?? max(1, $id * 2 - 1)),
                'rgt' => (int) ($row['rgt'] ?? max(2, $id * 2)),
                'level' => (int) ($row['level'] ?? 0),
                'image' => $this->blankToNull($row['images'] ?? null),
                'icon' => $this->blankToNull($row['icon'] ?? null),
                'album' => '',
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'follow' => 2,
                'order' => (int) ($row['order'] ?? 0),
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'attribute' => $this->toJson([]),
                'check' => 0,
                'sort' => 0,
                'short_name' => $this->blankToNull($row['title'] ?? null),
                ...$timestamps,
            ], $commit, 'products_catalogues', $id);

            $this->store('product_catalogue_language', [
                'product_catalogue_id' => $id,
                'language_id' => $languageId,
                'url' => $canonical,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Product catalogue {$id}",
                'description' => $this->blankToNull($row['description'] ?? null) ?? '',
                'content' => '',
                'meta_title' => $this->blankToNull($row['meta_title'] ?? null) ?? '',
                'meta_keyword' => $this->blankToNull($row['meta_keyword'] ?? null) ?? '',
                'meta_description' => $this->blankToNull($row['meta_description'] ?? null) ?? '',
                'canonical' => $canonical,
                ...$timestamps,
            ], $commit, 'products_catalogues', $id);

            $this->storeRouter('routers', $canonical, $id, $languageId, 'ProductCatalogueController', $commit, 'products_catalogues', $id, $timestamps);
        }
    }

    private function migrateProducts(bool $commit): void
    {
        $hasComboPrice = Schema::hasColumn('products', 'combo_price');

        foreach ($this->legacy['products'] ?? [] as $row) {
            $id = (int) $row['id'];
            $languageId = $this->languageId($row['alanguage'] ?? null);
            $catalogueId = (int) ($row['cataloguesid'] ?? 0);
            $timestamps = $this->timestamps($row);
            $canonical = $this->uniqueCanonical('ProductController', $languageId, $row['canonical'] ?? $row['slug'] ?? $row['title'] ?? "product-{$id}", $id);
            $attributeMap = $this->productAttributes[$id] ?? [];
            $this->archiveMappedRow('products', $row, 'products', $id);

            $payload = [
                'id' => $id,
                'product_catalogue_id' => $catalogueId,
                'image' => $this->blankToNull($row['images'] ?? null),
                'icon' => null,
                'album' => $this->legacyJsonOrEmpty($row['albums'] ?? null),
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'follow' => 2,
                'order' => (int) ($row['order'] ?? 0),
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'code' => $this->blankToNull($row['code'] ?? null) ?? '0',
                'made_in' => null,
                'price' => (int) ($row['price'] ?? 0),
                'stock' => 0,
                'attributeCatalogue' => $this->toJson(array_keys($attributeMap)),
                'attribute' => $this->toJson($attributeMap),
                'variant' => '',
                'qrcode' => '',
                'warranty' => null,
                'check' => 0,
                'iframe' => '',
                'seller_id' => null,
                'link' => null,
                'total_lesson' => 0,
                'duration' => '',
                'lession_content' => null,
                'chapter' => null,
                'percent' => null,
                'ml' => null,
                ...$timestamps,
            ];

            if ($hasComboPrice) {
                $payload['combo_price'] = ((int) ($row['saleoff'] ?? 0) > 0) ? (int) $row['saleoff'] : null;
            }

            $this->store('products', $payload, $commit, 'products', $id);

            $this->store('product_language', [
                'product_id' => $id,
                'language_id' => $languageId,
                'url' => $canonical,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Product {$id}",
                'description' => $this->blankToNull($row['description'] ?? null) ?? '',
                'content' => $this->blankToNull($row['content'] ?? null) ?? '',
                'meta_title' => $this->blankToNull($row['meta_title'] ?? null) ?? '',
                'meta_keyword' => $this->blankToNull($row['meta_keyword'] ?? null) ?? '',
                'meta_description' => $this->blankToNull($row['meta_description'] ?? null) ?? '',
                'canonical' => $canonical,
                ...$timestamps,
            ], $commit, 'products', $id);

            $this->attachCatalogues('product_catalogue_product', 'product_catalogue_id', 'product_id', $row['catalogues'] ?? null, $catalogueId, $id, $commit, 'products');
            $this->storeRouter('routers', $canonical, $id, $languageId, 'ProductController', $commit, 'products', $id, $timestamps);
        }

        if ($commit) {
            $this->updateProductCatalogueAttributes();
        }
    }

    private function migrateCatalogueRelationships(bool $commit): void
    {
        foreach ($this->legacy['catalogues_relationship'] ?? [] as $row) {
            $module = strtolower((string) ($row['modules'] ?? ''));
            $catalogueId = (int) ($row['cataloguesid'] ?? 0);
            $moduleId = (int) ($row['modulesid'] ?? 0);

            if ($module === 'articles') {
                $this->archiveMappedRow('catalogues_relationship', $row, 'post_catalogue_post', null, 'Legacy catalogue relationship mapped to post pivot.');
                $this->storePivotUnique('post_catalogue_post', 'post_catalogue_id', 'post_id', $catalogueId, $moduleId, $commit, 'catalogues_relationship', $this->sourcePk($row));
                continue;
            }

            if ($module === 'products') {
                $this->archiveMappedRow('catalogues_relationship', $row, 'product_catalogue_product', null, 'Legacy catalogue relationship mapped to product pivot.');
                $this->storePivotUnique('product_catalogue_product', 'product_catalogue_id', 'product_id', $catalogueId, $moduleId, $commit, 'catalogues_relationship', $this->sourcePk($row));
                continue;
            }

            $this->archiveRows('catalogues_relationship', [$row], $commit, null, null, 'No direct target for legacy catalogue relationship module: ' . $module);
        }
    }

    private function migrateProvinces(bool $commit): void
    {
        $rows = $this->legacy['province'] ?? [];
        $byId = [];
        $topLevel = [];

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $byId[$id] = $row;
            if ((int) ($row['parentid'] ?? 0) === 0) {
                $topLevel[$id] = true;
            }
        }

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $parentId = (int) ($row['parentid'] ?? 0);
            $timestamps = [
                'created_at' => now()->toDateTimeString(),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
            ];

            if ($parentId === 0) {
                $this->archiveMappedRow('province', $row, 'provinces', $id);
                $this->store('provinces', [
                    'code' => $id,
                    'name' => $this->blankToNull($row['title'] ?? null) ?? "Province {$id}",
                    ...$timestamps,
                ], $commit, 'province', $id);
                continue;
            }

            if (isset($topLevel[$parentId])) {
                $this->archiveMappedRow('province', $row, 'districts', $id);
                $this->store('districts', [
                    'code' => $id,
                    'name' => $this->blankToNull($row['title'] ?? null) ?? "District {$id}",
                    'province_code' => $parentId,
                    ...$timestamps,
                ], $commit, 'province', $id);
                continue;
            }

            $district = $byId[$parentId] ?? null;
            if ($district === null) {
                $this->archiveRows('province', [$row], $commit, null, null, 'Missing parent location.');
                $this->missingFk('province', $id, 'province', $parentId);
                continue;
            }

            $this->archiveMappedRow('province', $row, 'wards', $id);
            $this->store('wards', [
                'code' => $id,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Ward {$id}",
                'province_code' => (int) ($district['parentid'] ?? 0),
                'district_code' => $parentId,
                ...$timestamps,
            ], $commit, 'province', $id);
        }
    }

    private function migrateContacts(bool $commit): void
    {
        foreach ($this->legacy['contacts'] ?? [] as $row) {
            $id = (int) $row['id'];
            $this->archiveMappedRow('contacts', $row, 'contacts', $id);
            $this->store('contacts', [
                'id' => $id,
                'name' => $this->blankToNull($row['fullname'] ?? null),
                'phone' => $this->blankToNull($row['phone'] ?? null),
                'address' => $this->blankToNull($row['address'] ?? null),
                'gender' => 0,
                'product_id' => null,
                'post_id' => null,
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'trash' => $this->trash($row),
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
                'deleted_at' => $this->deletedAt($row),
                'type' => (int) ($row['receiverid'] ?? 0) ?: null,
                'message' => $this->blankToNull($row['message'] ?? null),
                'email' => $this->blankToNull($row['email'] ?? null) ?? '',
            ], $commit, 'contacts', $id);
        }
    }

    private function migrateOrders(bool $commit): void
    {
        $existingProducts = [];
        foreach ($this->legacy['products'] ?? [] as $product) {
            $existingProducts[(int) $product['id']] = true;
        }

        foreach ($this->legacy['payments'] ?? [] as $row) {
            $id = (int) $row['id'];
            $status = (string) ($row['status'] ?? 'wait');
            $this->archiveMappedRow('payments', $row, 'orders', $id);
            $this->store('orders', [
                'id' => $id,
                'code' => 'LEGACY-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT),
                'fullname' => $this->blankToNull($row['fullname'] ?? null) ?? 'Legacy Customer',
                'phone' => $this->blankToNull($row['phone'] ?? null) ?? '',
                'email' => $this->blankToNull($row['email'] ?? null) ?? '',
                'province_id' => $this->blankToNull($row['cityid'] ?? null),
                'district_id' => $this->blankToNull($row['districtid'] ?? null),
                'ward_id' => null,
                'address' => $this->blankToNull($row['address'] ?? null) ?? '',
                'description' => $this->blankToNull($row['message'] ?? null),
                'promotion' => $this->toJson([]),
                'cart' => $this->legacyJsonOrEmpty($row['data'] ?? null, true),
                'customer_id' => ((int) ($row['userid'] ?? 0) > 0) ? (int) $row['userid'] : null,
                'guest_cookie' => null,
                'method' => 'cod',
                'confirm' => $status === 'success' ? 'confirm' : 'pending',
                'payment' => $status === 'success' ? 'paid' : 'unpaid',
                'delivery' => $status === 'success' ? 'success' : 'pending',
                'shipping' => 0,
                'deleted_at' => $this->deletedAt($row),
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
                'seller_id' => null,
                'point_added' => 0,
                'point_value' => 0,
                'point_used' => 0,
                'point_used_deducted' => 0,
            ], $commit, 'payments', $id);
        }

        foreach ($this->legacy['payments_items'] ?? [] as $row) {
            $id = (int) $row['id'];
            $productId = (int) ($row['productsid'] ?? 0);
            $this->archiveMappedRow('payments_items', $row, 'order_product', $id);
            if (!isset($existingProducts[$productId])) {
                $this->missingFk('payments_items', $id, 'products', $productId);
                continue;
            }

            $this->store('order_product', [
                'id' => $id,
                'order_id' => (int) ($row['paymentsid'] ?? 0),
                'product_id' => $productId,
                'uuid' => null,
                'name' => "Legacy product {$productId}",
                'qty' => (int) ($row['quantity'] ?? 0),
                'price' => (int) ($row['price'] ?? 0),
                'priceOriginal' => (int) ($row['price'] ?? 0),
                'option' => $this->legacyJsonOrEmpty($row['option'] ?? null, true),
            ], $commit, 'payments_items', $id);
        }
    }

    private function migrateMenus(bool $commit): void
    {
        foreach ($this->legacy['navigations_positions'] ?? [] as $row) {
            $id = (int) $row['id'];
            $this->archiveMappedRow('navigations_positions', $row, 'menu_catalogues', $id);
            $this->store('menu_catalogues', [
                'id' => $id,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Legacy menu catalogue {$id}",
                'keyword' => $this->slug($row['canonical'] ?? $row['title'] ?? "legacy-menu-{$id}"),
                'publish' => $this->mapPublish($row['publish'] ?? null),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
            ], $commit, 'navigations_positions', $id);
        }

        $menuRows = [];
        $parentPositionByMenuId = [];
        foreach ($this->legacy['navigations_menus'] ?? [] as $row) {
            $id = (int) $row['id'];
            $positionId = (int) ($row['positionsid'] ?? 1) ?: 1;
            $parentPositionByMenuId[$id] = $positionId;
            $menuRows[] = [$row, 0, $id, $positionId];
        }

        $nextId = 100000;
        foreach ($this->legacy['navigations_menus_items'] ?? [] as $row) {
            $parentId = (int) ($row['menusid'] ?? 0);
            $positionId = $parentPositionByMenuId[$parentId] ?? ((int) ($row['positionsid'] ?? 1) ?: 1);
            $menuRows[] = [$row, $parentId, $nextId++, $positionId];
        }

        foreach ($menuRows as [$row, $parentId, $id, $positionId]) {
            $languageId = $this->languageId($row['alanguage'] ?? null);
            $timestamps = $this->timestamps($row);
            $sourceTable = array_key_exists('menusid', $row) ? 'navigations_menus_items' : 'navigations_menus';
            $this->archiveMappedRow($sourceTable, $row, 'menus', $id);

            $this->store('menus', [
                'id' => $id,
                'parent_id' => $parentId,
                'menu_catalogue_id' => $positionId,
                'lft' => max(1, $id * 2 - 1),
                'rgt' => max(2, $id * 2),
                'level' => $parentId > 0 ? 1 : 0,
                'type' => $this->blankToNull($row['modules'] ?? null),
                'image' => null,
                'icon' => null,
                'album' => '',
                'publish' => $this->mapPublish($row['publish'] ?? 1),
                'order' => (int) ($row['order'] ?? 0),
                'user_id' => $this->userId($row),
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                ...$timestamps,
            ], $commit, $sourceTable, $id);

            $this->store('menu_language', [
                'menu_id' => $id,
                'language_id' => $languageId,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Menu {$id}",
                'canonical' => $this->blankToNull($row['href'] ?? null),
                ...$timestamps,
            ], $commit, $sourceTable, $id);
        }
    }

    private function migrateSlides(bool $commit): void
    {
        $itemsByGroup = [];
        foreach ($this->legacy['slides'] ?? [] as $row) {
            $groupId = (int) ($row['groupsid'] ?? 0);
            $languageId = $this->languageId($row['alanguage'] ?? null);
            $this->archiveMappedRow('slides', $row, 'slides', $groupId, 'Legacy slide item folded into slides.item JSON.');
            $itemsByGroup[$groupId][$languageId][] = [
                'image' => $this->blankToNull($row['image'] ?? null),
                'name' => $this->blankToNull($row['title'] ?? null),
                'description' => $this->blankToNull($row['description'] ?? null),
                'canonical' => $this->blankToNull($row['url'] ?? null),
                'alt' => $this->blankToNull($row['title'] ?? null),
                'window' => '',
            ];
        }

        foreach ($this->legacy['slides_groups'] ?? [] as $row) {
            $id = (int) $row['id'];
            $this->archiveMappedRow('slides_groups', $row, 'slides', $id);
            $this->store('slides', [
                'id' => $id,
                'name' => $this->blankToNull($row['title'] ?? null) ?? "Slide {$id}",
                'keyword' => $this->slug($row['keyword'] ?? $row['title'] ?? "legacy-slide-{$id}"),
                'description' => $this->blankToNull($row['description'] ?? null),
                'item' => $this->toJson($itemsByGroup[$id] ?? []),
                'setting' => $this->toJson([
                    'width' => null,
                    'height' => null,
                    'animation' => 'fade',
                    'arrow' => 'accept',
                    'navigate' => 'dots',
                    'autoplay' => 'accept',
                    'pauseHover' => 'accept',
                    'animationDelay' => null,
                    'animationSpeed' => null,
                ]),
                'short_code' => '',
                'trash' => $this->trash($row),
                'deleted_at' => $this->deletedAt($row),
                'created_at' => $this->dateOrNow($row['created'] ?? null),
                'updated_at' => $this->dateOrNow($row['updated'] ?? null),
                'publish' => $this->mapPublish($row['publish'] ?? null),
            ], $commit, 'slides_groups', $id);
        }
    }

    private function migrateSystems(bool $commit): void
    {
        foreach ($this->legacy['systems'] ?? [] as $index => $row) {
            $createdAt = $this->dateOrNow($row['created'] ?? null);
            $updatedAt = $this->dateOrNow($row['updated'] ?? null);
            $this->archiveMappedRow('systems', $row, 'systems', null);
            $this->store('systems', [
                'language_id' => 1,
                'user_id' => $this->fallbackUserId(),
                'keyword' => Str::limit((string) ($row['keyword'] ?? "legacy_system_{$index}"), 50, ''),
                'content' => $this->blankToNull($row['content'] ?? null),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ], $commit, 'systems', $row['keyword'] ?? (string) $index);

            if ($this->blankToNull($row['content2'] ?? null) !== null) {
                $this->store('systems', [
                    'language_id' => 1,
                    'user_id' => $this->fallbackUserId(),
                    'keyword' => Str::limit((string) ($row['keyword'] ?? "legacy_system_{$index}") . '_content2', 50, ''),
                    'content' => $this->blankToNull($row['content2'] ?? null),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ], $commit, 'systems', ($row['keyword'] ?? (string) $index) . '_content2');
            }
        }
    }

    private function updateProductCatalogueAttributes(): void
    {
        $attributesByCatalogue = [];
        foreach ($this->productAttributes as $productId => $groups) {
            $product = DB::table('products')->select('product_catalogue_id')->where('id', $productId)->first();
            if (!$product) {
                continue;
            }

            foreach ($groups as $attributeCatalogueId => $attributeIds) {
                $attributesByCatalogue[(int) $product->product_catalogue_id][$attributeCatalogueId] = array_values(array_unique(array_merge(
                    $attributesByCatalogue[(int) $product->product_catalogue_id][$attributeCatalogueId] ?? [],
                    $attributeIds
                )));
            }
        }

        foreach ($attributesByCatalogue as $catalogueId => $attributeMap) {
            DB::table('product_catalogues')->where('id', $catalogueId)->update([
                'attribute' => $this->toJson($attributeMap),
            ]);
        }
    }

    private function attachCatalogues(string $table, string $catalogueColumn, string $modelColumn, ?string $legacyJson, int $fallbackCatalogueId, int $modelId, bool $commit, string $sourceTable): void
    {
        $catalogueIds = [];
        $decoded = $this->decodeLegacyJson($legacyJson);
        if (is_array($decoded)) {
            $catalogueIds = array_map('intval', $decoded);
        }

        if ($fallbackCatalogueId > 0) {
            $catalogueIds[] = $fallbackCatalogueId;
        }

        $catalogueIds = array_values(array_unique(array_filter($catalogueIds)));
        foreach ($catalogueIds as $catalogueId) {
            $this->storePivotUnique($table, $catalogueColumn, $modelColumn, $catalogueId, $modelId, $commit, $sourceTable, $modelId);
        }
    }

    private function storePivotUnique(
        string $table,
        string $catalogueColumn,
        string $modelColumn,
        int $catalogueId,
        int $modelId,
        bool $commit,
        string $sourceTable,
        int|string|null $sourcePk = null
    ): void {
        if ($catalogueId <= 0 || $modelId <= 0) {
            return;
        }

        $key = "{$table}:{$catalogueId}:{$modelId}";
        if (isset($this->pivotKeys[$key])) {
            return;
        }

        $this->pivotKeys[$key] = true;
        $this->store($table, [
            $catalogueColumn => $catalogueId,
            $modelColumn => $modelId,
        ], $commit, $sourceTable, $sourcePk);
    }

    private function storeRouter(string $table, string $canonical, int $moduleId, int $languageId, string $controller, bool $commit, string $sourceTable, int|string|null $sourcePk, array $timestamps): void
    {
        $this->store($table, [
            'canonical' => $canonical,
            'module_id' => $moduleId,
            'controllers' => "App\\Http\\Controllers\\Frontend\\{$controller}",
            'created_at' => $timestamps['created_at'],
            'updated_at' => $timestamps['updated_at'],
            'language_id' => $languageId,
        ], $commit, $sourceTable, $sourcePk);
    }

    private function archiveRows(string $sourceTable, array $rows, bool $commit, ?string $targetTable, int|string|null $targetId, ?string $notes = null): void
    {
        $this->increment($this->report['archived_legacy_tables'], $sourceTable, count($rows));

        if (!$commit || empty($rows)) {
            return;
        }

        $now = now()->toDateTimeString();
        $payloads = [];
        foreach ($rows as $row) {
            $payloads[] = [
                'source_table' => $sourceTable,
                'source_pk' => $this->sourcePk($row),
                'payload' => $this->toJson($row),
                'migrated_to_table' => $targetTable,
                'migrated_to_id' => $targetId,
                'notes' => $notes,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($payloads) >= 500) {
                DB::table('legacy_import_records')->insert($payloads);
                $payloads = [];
            }
        }

        if (!empty($payloads)) {
            DB::table('legacy_import_records')->insert($payloads);
        }
    }

    private function archiveMappedRow(string $sourceTable, array $row, ?string $targetTable, int|string|null $targetId, ?string $notes = null): void
    {
        $this->archiveRows($sourceTable, [$row], $this->commit, $targetTable, $targetId, $notes);
    }

    private function store(string $table, array $payload, bool $commit, string $sourceTable, int|string|null $sourcePk = null): void
    {
        $payload = $this->filterPayloadForTable($table, $payload);

        if (!$commit) {
            $this->increment($this->report['seeded_rows'], $table);
            return;
        }

        try {
            DB::table($table)->insert($payload);
            $this->increment($this->report['seeded_rows'], $table);
        } catch (Throwable $exception) {
            $this->report['failed_rows'][] = [
                'source_table' => $sourceTable,
                'source_pk' => $sourcePk,
                'target_table' => $table,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function filterPayloadForTable(string $table, array $payload): array
    {
        if ($table === 'posts' && !array_key_exists('pubish', $payload) && array_key_exists('publish', $payload)) {
            $payload['pubish'] = $payload['publish'];
        }

        if ($table === 'post_catalogues') {
            if (!array_key_exists('pubish', $payload) && array_key_exists('publish', $payload)) {
                $payload['pubish'] = $payload['publish'];
            }
            if (!array_key_exists('parentid', $payload) && array_key_exists('parent_id', $payload)) {
                $payload['parentid'] = $payload['parent_id'];
            }
        }

        $columns = $this->columnsFor($table);
        if (empty($columns)) {
            return $payload;
        }

        return array_intersect_key($payload, array_flip($columns));
    }

    private function columnsFor(string $table): array
    {
        if (!array_key_exists($table, $this->tableColumns)) {
            $this->tableColumns[$table] = Schema::hasTable($table) ? Schema::getColumnListing($table) : [];
        }

        return $this->tableColumns[$table];
    }

    private function ensureBaseUser(): void
    {
        if (!Schema::hasTable('user_catalogues') || !Schema::hasTable('users')) {
            return;
        }

        if (!DB::table('user_catalogues')->where('id', 2)->exists()) {
            DB::table('user_catalogues')->insert([
                'id' => 2,
                'name' => 'Legacy administrators',
                'description' => 'Created by legacy migration.',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'publish' => 2,
            ]);
        }

        if (!DB::table('users')->where('id', 1)->exists()) {
            DB::table('users')->insert($this->filterPayloadForTable('users', [
                'id' => 1,
                'name' => 'Legacy Admin',
                'phone' => null,
                'province_id' => null,
                'district_id' => null,
                'ward_id' => null,
                'address' => null,
                'birthday' => null,
                'image' => null,
                'description' => 'Created by legacy migration.',
                'user_agent' => null,
                'ip' => null,
                'email' => 'legacy-admin@legacy.local',
                'email_verified_at' => null,
                'password' => Hash::make('legacy-import-password'),
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'user_catalogue_id' => 2,
                'deleted_at' => null,
                'publish' => 2,
            ]));
        }
    }

    private function ensureLanguages(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        $languages = [
            1 => ['name' => 'Tiếng Việt', 'canonical' => 'vn', 'current' => 1],
            2 => ['name' => 'Tiếng Anh', 'canonical' => 'en', 'current' => 0],
        ];

        foreach ($languages as $id => $language) {
            if (DB::table('languages')->where('id', $id)->exists()) {
                continue;
            }

            DB::table('languages')->insert($this->filterPayloadForTable('languages', [
                'id' => $id,
                'name' => $language['name'],
                'canonical' => $language['canonical'],
                'image' => '',
                'user_id' => $this->fallbackUserId(),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'publish' => 2,
                'current' => $language['current'],
            ]));
        }
    }

    private function ensureSource(bool $commit): void
    {
        if (!$commit || !Schema::hasTable('sources')) {
            return;
        }

        if (!DB::table('sources')->where('id', 3)->exists()) {
            DB::table('sources')->insert([
                'id' => 3,
                'name' => 'Legacy import',
                'keyword' => 'legacy-import',
                'description' => 'Created by legacy migration.',
                'publish' => 2,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function writeReport(): void
    {
        $reportPath = (string) ($this->option('report') ?: storage_path('app/karaoke-legacy-migration-report.json'));
        file_put_contents($reportPath, $this->toJson($this->report, JSON_PRETTY_PRINT));
    }

    private function printSummary(): void
    {
        $this->info('Legacy migration report written to: ' . ($this->option('report') ?: storage_path('app/karaoke-legacy-migration-report.json')));
        $this->line('Input tables: ' . count($this->report['input_rows']));
        $this->line('Target tables seeded: ' . count($this->report['seeded_rows']));
        $this->line('Archived legacy tables: ' . count($this->report['archived_legacy_tables']));
        $this->line('Failed rows: ' . count($this->report['failed_rows']));
    }

    private function uniqueCanonical(string $controller, int $languageId, string $canonical, int $legacyId): string
    {
        $base = $this->slug($canonical ?: "legacy-{$legacyId}");
        $key = $base;

        if (!isset($this->usedRouterCanonicals[$key])) {
            $this->usedRouterCanonicals[$key] = true;
            return $base;
        }

        $controllerSuffix = Str::of($controller)->replace('Controller', '')->kebab()->toString();
        $deduped = "{$base}-{$controllerSuffix}-legacy-{$legacyId}";
        $counter = 2;
        while (isset($this->usedRouterCanonicals[$deduped])) {
            $deduped = "{$base}-{$controllerSuffix}-legacy-{$legacyId}-{$counter}";
            $counter++;
        }

        $this->report['duplicate_canonicals'][] = [
            'controller' => $controller,
            'language_id' => $languageId,
            'canonical' => $base,
            'deduped' => $deduped,
        ];

        $this->usedRouterCanonicals[$deduped] = true;
        return $deduped;
    }

    private function timestamps(array $row): array
    {
        return [
            'created_at' => $this->dateOrNow($row['created'] ?? null),
            'updated_at' => $this->dateOrNow($row['updated'] ?? null),
        ];
    }

    private function mapPublish(mixed $value): int
    {
        return (int) $value === 1 ? 2 : 1;
    }

    private function deletedAt(array $row): ?string
    {
        return (int) ($row['trash'] ?? 0) === 1 ? now()->toDateTimeString() : null;
    }

    private function trash(array $row): int
    {
        return (int) ($row['trash'] ?? 0);
    }

    private function dateOrNow(mixed $value): string
    {
        $value = $this->blankToNull($value);
        if ($value === null || $value === '0000-00-00 00:00:00') {
            return now()->toDateTimeString();
        }

        return (string) $value;
    }

    private function blankToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function languageId(?string $legacyLanguage): int
    {
        return self::LANGUAGE_MAP[strtolower((string) $legacyLanguage)] ?? 1;
    }

    private function userId(array $row): int
    {
        $legacyId = (int) ($row['userid_created'] ?? 0);
        return $this->legacyUserMap[$legacyId] ?? $this->fallbackUserId();
    }

    private function fallbackUserId(): int
    {
        return (int) (DB::table('users')->min('id') ?: 1);
    }

    private function validPassword(?string $password): bool
    {
        return is_string($password) && (str_starts_with($password, '$2y$') || str_starts_with($password, '$argon2'));
    }

    private function slug(mixed $value): string
    {
        return Str::slug((string) $value) ?: 'legacy-item';
    }

    private function legacyJsonOrEmpty(mixed $value, bool $wrapInvalid = false): string
    {
        $decoded = $this->decodeLegacyJson($value);
        if ($decoded !== null) {
            return $this->toJson($decoded);
        }

        $value = $this->blankToNull($value);
        if ($wrapInvalid && $value !== null) {
            return $this->toJson(['legacy_raw' => $value]);
        }

        return $wrapInvalid ? $this->toJson([]) : '';
    }

    private function decodeLegacyJson(mixed $value): mixed
    {
        $value = $this->blankToNull($value);
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    private function toJson(mixed $value, int $flags = 0): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | $flags) ?: 'null';
    }

    private function sourcePk(array $row): ?string
    {
        foreach (['id', 'code', 'canonical', 'keyword'] as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return (string) $row[$key];
            }
        }

        if (isset($row['modules'], $row['cataloguesid'], $row['modulesid'])) {
            return implode(':', [$row['modules'], $row['cataloguesid'], $row['modulesid']]);
        }

        if (isset($row['productsid'], $row['attrid'])) {
            return implode(':', [$row['productsid'], $row['attrid']]);
        }

        return null;
    }

    private function increment(array &$bucket, string $key, int $amount = 1): void
    {
        $bucket[$key] = ($bucket[$key] ?? 0) + $amount;
    }

    private function missingFk(string $sourceTable, int|string|null $sourcePk, string $targetTable, int|string|null $targetId): void
    {
        $this->report['missing_foreign_keys'][] = [
            'source_table' => $sourceTable,
            'source_pk' => $sourcePk,
            'target_table' => $targetTable,
            'target_id' => $targetId,
        ];
    }
}
