<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class AssetVersioningService
{
    protected string $manifestPath;

    public function __construct()
    {
        $this->manifestPath = public_path('build/manifest.json');
    }

    public function getVersionedAsset(string $asset): string
    {
        if (!File::exists($this->manifestPath)) {
            return asset($asset);
        }

        $manifest = json_decode(File::get($this->manifestPath), true);
        $versionedAsset = $manifest[$asset] ?? $asset;

        return asset('build/' . $versionedAsset);
    }

    public function getManifestVersion(): string
    {
        if (!File::exists($this->manifestPath)) {
            return '';
        }

        $manifest = json_decode(File::get($this->manifestPath), true);
        return md5(json_encode($manifest));
    }

    public function getAssetHash(string $assetPath): string
    {
        $fullPath = public_path($assetPath);

        if (!File::exists($fullPath)) {
            return '';
        }

        return md5_file($fullPath);
    }

    /**
     * Get all versioned assets
     */
    public function getAllVersionedAssets(): array
    {
        if (!File::exists($this->manifestPath)) {
            return [];
        }

        return json_decode(File::get($this->manifestPath), true);
    }

    /**
     * Check if manifest exists and is valid
     */
    public function isValidManifest(): bool
    {
        if (!File::exists($this->manifestPath)) {
            return false;
        }

        try {
            json_decode(File::get($this->manifestPath), true, 512, JSON_THROW_ON_ERROR);
            return true;
        } catch (\JsonException $e) {
            return false;
        }
    }
}
