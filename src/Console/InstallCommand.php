<?php

namespace Susoft\LicenseGuard\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'license-guard:install
                            {--key= : License key cho site này}
                            {--product= : Mã sản phẩm (product_code)}';

    protected $description = 'Cài đặt License Guard: nhập license key & product_code vào file .env';

    public function handle(): int
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->error('.env không tồn tại. Hãy copy từ .env.example trước.');
            return self::FAILURE;
        }

        $key = $this->option('key') ?: env('LICENSE_GUARD_KEY');
        $product = $this->option('product') ?: env('LICENSE_GUARD_PRODUCT_CODE');

        // Hỏi nếu chưa có
        if (! $key) {
            $key = $this->ask('Nhập LICENSE_GUARD_KEY cho site này');
        }

        if (! $product) {
            $product = $this->ask('Nhập LICENSE_GUARD_PRODUCT_CODE (mã sản phẩm trên license server)');
        }

        if (! $key || ! $product) {
            $this->error('LICENSE_GUARD_KEY và LICENSE_GUARD_PRODUCT_CODE không được để trống.');
            return self::FAILURE;
        }

        $env = file_get_contents($envPath);

        $env = $this->setEnvValue($env, 'LICENSE_GUARD_KEY', $key);
        $env = $this->setEnvValue($env, 'LICENSE_GUARD_PRODUCT_CODE', $product);

        // Các biến mặc định khác (nếu chưa có thì thêm)
        $env = $this->setEnvValue($env, 'LICENSE_GUARD_CACHE_TTL', env('LICENSE_GUARD_CACHE_TTL', 300), false);
        $env = $this->setEnvValue($env, 'LICENSE_GUARD_GRACE_ON_ERROR', env('LICENSE_GUARD_GRACE_ON_ERROR', false), false);
        $env = $this->setEnvValue($env, 'LICENSE_GUARD_DISABLED', env('LICENSE_GUARD_DISABLED', false), false);

        file_put_contents($envPath, $env);

        $this->info('Đã cập nhật LICENSE_GUARD_* trong .env');

        return self::SUCCESS;
    }

    /**
     * Cập nhật hoặc thêm mới một biến env.
     *
     * @param  string  $env
     * @param  string  $key
     * @param  mixed   $value
     * @param  bool    $overwrite  Có ghi đè nếu đã tồn tại không
     * @return string
     */
    protected function setEnvValue(string $env, string $key, $value, bool $overwrite = true): string
    {
        $value = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        $line  = $key . '=' . $value;

        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

        if (preg_match($pattern, $env)) {
            if (! $overwrite) {
                // Đã có sẵn và không cho ghi đè → giữ nguyên
                return $env;
            }

            return preg_replace($pattern, $line, $env);
        }

        return rtrim($env, "\r\n") . PHP_EOL . $line . PHP_EOL;
    }
}
