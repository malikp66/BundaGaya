<?php

namespace Database\Seeders;

class ImageGenerator
{
    private static string $outputDir;

    public static function init(): void
    {
        self::$outputDir = storage_path('app/public/products');

        if (!is_dir(self::$outputDir)) {
            mkdir(self::$outputDir, 0755, true);
        }
    }

    public static function generate(string $productName, array $color, int $variant = 0): string
    {
        self::init();

        $filename = \Illuminate\Support\Str::slug($productName) . '-' . ($variant + 1) . '.jpg';
        $filepath = self::$outputDir . '/' . $filename;

        if (file_exists($filepath)) {
            return 'products/' . $filename;
        }

        $width = 600;
        $height = 800;
        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        imagefill($image, 0, 0, $bgColor);

        $darkerColor = imagecolorallocate(
            $image,
            max(0, $color[0] - 40),
            max(0, $color[1] - 40),
            max(0, $color[2] - 40)
        );

        for ($i = 0; $i < 5; $i++) {
            $x = rand(0, $width);
            $y = rand(0, $height);
            $size = rand(50, 200);
            $alpha = rand(10, 30);
            $circleColor = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
            imagefilledellipse($image, $x, $y, $size, $size, $circleColor);
        }

        $lightColor = imagecolorallocate($image, 255, 255, 255);
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 80);

        $fontSize = 5;
        $lines = self::wrapText($productName, 25);
        $totalHeight = count($lines) * ($fontSize + 6);
        $startY = ($height - $totalHeight) / 2;

        foreach ($lines as $i => $line) {
            $lineWidth = imagefontwidth($fontSize) * strlen($line);
            $x = ($width - $lineWidth) / 2;
            $y = $startY + ($i * ($fontSize + 6));

            imagestring($image, $fontSize, (int)$x + 1, (int)$y + 1, $line, $shadowColor);
            imagestring($image, $fontSize, (int)$x, (int)$y, $line, $lightColor);
        }

        $brandText = 'BundaGaya';
        $brandWidth = imagefontwidth(3) * strlen($brandText);
        imagestring($image, 3, (int)(($width - $brandWidth) / 2), $height - 40, $brandText, $lightColor);

        $variantLabel = $variant === 0 ? 'Tampak Depan' : ($variant === 1 ? 'Tampak Samping' : 'Detail');
        $labelWidth = imagefontwidth(2) * strlen($variantLabel);
        imagestring($image, 2, (int)(($width - $labelWidth) / 2), $height - 25, $variantLabel, $lightColor);

        imagejpeg($image, $filepath, 85);
        imagedestroy($image);

        return 'products/' . $filename;
    }

    private static function wrapText(string $text, int $maxLength): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) > $maxLength) {
                if ($currentLine !== '') {
                    $lines[] = trim($currentLine);
                }
                $currentLine = $word;
            } else {
                $currentLine .= ' ' . $word;
            }
        }

        if (trim($currentLine) !== '') {
            $lines[] = trim($currentLine);
        }

        return $lines;
    }

    public static function colors(): array
    {
        return [
            'rose_gold'   => [183, 110, 121],
            'navy'        => [25, 25, 112],
            'emerald'     => [0, 130, 100],
            'dusty_pink'  => [194, 138, 148],
            'champagne'   => [198, 176, 138],
            'burgundy'    => [128, 0, 32],
            'lilac'       => [170, 152, 200],
            'ivory'       => [240, 230, 210],
            'scarlet'     => [200, 30, 50],
            'gold'        => [195, 160, 80],
            'black'       => [30, 30, 30],
            'pastel_blue' => [160, 190, 220],
            'sage'        => [140, 170, 140],
            'brown'       => [130, 90, 60],
            'natural'     => [210, 195, 170],
            'white'       => [235, 235, 235],
            'pink'        => [230, 150, 170],
            'gray'        => [140, 140, 140],
            'striped'     => [100, 140, 180],
            'cream'       => [235, 225, 200],
        ];
    }
}
