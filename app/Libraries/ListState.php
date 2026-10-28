<?php

namespace App\Libraries;

/**
 * The sort/filter/page state of a list page, read from the URL query string.
 *
 * Replaces the hand-built "?sort=..&dir=..&limit=..&offset=..{$filter}" strings in the CI3
 * controllers and views. Every value is checked against what the page allows, so URL
 * parameters never reach SQL unchecked.
 */
final class ListState
{
    /**
     * @param array<string, string> $filters column => text (only non-empty values)
     * @param array<string, string> $extra   other allowed parameters, e.g. ['cmd' => 'GenMod']
     */
    public function __construct(
        public readonly string $baseUrl,
        public readonly int $limit,
        public readonly int $page,
        public readonly string $sort,
        public readonly string $dir,
        public readonly array $filters = [],
        public readonly array $extra = [],
    ) {
    }

    /**
     * @param array<string, mixed>               $get        query string values
     * @param list<string>                       $sortable   columns the list can sort by
     * @param list<string>                       $filterable columns the list can filter on
     * @param array<string, list<string>>        $extra      parameter => allowed values
     */
    public static function fromInput(
        array $get,
        string $baseUrl,
        int $defaultLimit,
        array $sortable,
        string $defaultSort,
        array $filterable = [],
        array $extra = [],
        string $defaultDir = 'asc',
    ): self {
        $limit = (int) ($get['limit'] ?? 0) ?: $defaultLimit;
        $sort  = (string) ($get['sort'] ?? '');
        $dir   = strtolower((string) ($get['dir'] ?? $defaultDir));

        $extraValues = [];
        foreach ($extra as $name => $allowed) {
            $value = (string) ($get[$name] ?? '');
            if ($value !== '' && in_array($value, $allowed, true)) {
                $extraValues[$name] = $value;
            }
        }

        return new self(
            $baseUrl,
            min(max($limit, 1), 500),
            max(1, (int) ($get['page'] ?? 1)),
            in_array($sort, $sortable, true) ? $sort : $defaultSort,
            in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc',
            self::filtersFrom($get, $filterable),
            $extraValues,
        );
    }

    /**
     * Non-empty, trimmed filter values from GET or POST data.
     *
     * @param list<string> $filterable
     *
     * @return array<string, string>
     */
    public static function filtersFrom(?array $input, array $filterable): array
    {
        $filters = [];

        foreach ($filterable as $field) {
            $value = trim((string) ($input[$field] ?? ''));
            if ($value !== '') {
                $filters[$field] = $value;
            }
        }

        return $filters;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function withFilters(array $filters): self
    {
        return new self($this->baseUrl, $this->limit, 1, $this->sort, $this->dir, $filters, $this->extra);
    }

    /**
     * Query parameters for this list (without page), with $override applied.
     *
     * @return array<string, int|string>
     */
    public function query(array $override = []): array
    {
        return array_merge(
            ['sort' => $this->sort, 'dir' => $this->dir, 'limit' => $this->limit],
            $this->extra,
            $this->filters,
            $override,
        );
    }

    /**
     * URL of this list (page 1 unless $override sets 'page').
     */
    public function url(array $override = []): string
    {
        return $this->baseUrl . '?' . http_build_query($this->query($override));
    }

    /**
     * URL of exactly this page, used to come back after add/edit/delete.
     */
    public function currentUrl(): string
    {
        return $this->url(['page' => $this->page]);
    }

    /**
     * Header link: the current column flips direction, another column starts ascending.
     */
    public function sortUrl(string $column): string
    {
        $dir = ($this->sort === $column && $this->dir === 'asc') ? 'desc' : 'asc';

        return $this->url(['sort' => $column, 'dir' => $dir]);
    }
}
