<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConfigController extends Controller
{
    private const OPENING_TIME_KEY = 'opening_time';
    private const CLOSING_TIME_KEY = 'closing_time';
    private const DEFAULT_OPENING_TIME = '08:00';
    private const DEFAULT_CLOSING_TIME = '23:59';

    public function show()
    {
        return response()->json($this->buildConfigPayload());
    }

    public function update(Request $request)
    {
        $this->ensureCan('users index');

        $validated = $request->validate([
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
        ]);

        Setting::query()->updateOrCreate(
            ['key' => self::OPENING_TIME_KEY],
            ['value' => $validated['opening_time']]
        );

        Setting::query()->updateOrCreate(
            ['key' => self::CLOSING_TIME_KEY],
            ['value' => $validated['closing_time']]
        );

        return response()->json($this->buildConfigPayload());
    }

    private function buildConfigPayload(): array
    {
        $settings = Setting::query()
            ->whereIn('key', [self::OPENING_TIME_KEY, self::CLOSING_TIME_KEY])
            ->pluck('value', 'key');

        $openingTime = (string) ($settings[self::OPENING_TIME_KEY] ?? self::DEFAULT_OPENING_TIME);
        $closingTime = (string) ($settings[self::CLOSING_TIME_KEY] ?? self::DEFAULT_CLOSING_TIME);

        $defaultConfig = $this->buildDefaultConfig($openingTime, $closingTime);

        return [
            'opening_time' => $openingTime,
            'closing_time' => $closingTime,
            'default_config' => $defaultConfig,
        ];
    }

    private function buildDefaultConfig(string $openingTime, string $closingTime): array
    {
        $today = Carbon::today();
        $date = $today->toDateString();

        $fromDateTime = Carbon::parse("{$date} {$openingTime}:00");
        $toDateTime = Carbon::parse("{$date} {$closingTime}:00");

        if ($toDateTime->lessThanOrEqualTo($fromDateTime)) {
            $toDateTime->addDay();
        }

        return [
            'from_datetime' => $fromDateTime->toDateTimeString(),
            'to_datetime' => $toDateTime->toDateTimeString(),
            'from_date' => $fromDateTime->toDateString(),
            'to_date' => $toDateTime->toDateString(),
        ];
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }
}
