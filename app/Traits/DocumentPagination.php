<?php

namespace App\Traits;

/**
 * DocumentPagination Trait
 * Handles pagination of large document content
 */
trait DocumentPagination
{
    protected int $pageSize = 5000; // characters per page
    protected int $currentPage = 1;

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $size): self
    {
        $this->pageSize = max($size, 1000); // minimum 1000 chars
        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $page): self
    {
        $this->currentPage = max($page, 1);
        return $this;
    }

    public function paginateContent(string $content): array
    {
        $totalLength = strlen($content);
        $totalPages = ceil($totalLength / $this->pageSize);

        $startChar = ($this->currentPage - 1) * $this->pageSize;
        $pageContent = substr($content, $startChar, $this->pageSize);

        return [
            'content' => $pageContent,
            'currentPage' => $this->currentPage,
            'totalPages' => $totalPages,
            'totalLength' => $totalLength,
            'startChar' => $startChar,
            'endChar' => $startChar + strlen($pageContent),
            'hasNextPage' => $this->currentPage < $totalPages,
            'hasPreviousPage' => $this->currentPage > 1,
        ];
    }

    public function nextPage(): array
    {
        $this->currentPage++;
        return $this->paginateContent($this->document->content);
    }

    public function previousPage(): array
    {
        $this->currentPage = max($this->currentPage - 1, 1);
        return $this->paginateContent($this->document->content);
    }

    public function goToPage(int $page): array
    {
        $this->setCurrentPage($page);
        return $this->paginateContent($this->document->content);
    }
}
