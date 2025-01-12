<?php
/**
 * Gauntlet MUD - Lisp functions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use MadLisp\Env;
use MadLisp\Hash;
use MadLisp\Lisp as RealLisp;
use MadLisp\LispFactory;
use MadLisp\Vector;

use Gauntlet\BaseObject;
use Gauntlet\Item;
use Gauntlet\LispFuncs;
use Gauntlet\Living;

class Lisp
{
    protected static RealLisp $lisp;

    public static function initialize(LispFuncs $funcs): void
    {
        $factory = new LispFactory();
        self::$lisp = $factory->make(true);

        $funcs->register(self::$lisp->getEnv());

        $files = array_merge([
            APP_DIR . 'bootstrap.lisp',
        ], glob(DATA_DIR . 'lisp/*.lisp'));

        foreach ($files as $file) {
            self::evalFile($file);
        }
    }

    public static function eval(BaseObject $source, string $code)
    {
        $env = $source->createLispEnv(self::$lisp->getEnv());
        return self::evalWithEnv($source, $code, $env);
    }

    public static function evalWithData(BaseObject $source, string $code, array $data)
    {
        $parent = $source->createLispEnv(self::$lisp->getEnv());
        $env = new Env('temp', $parent);

        foreach ($data as $key => $val) {
            // Convert arrays to Lisp types
            if (is_array($val)) {
                if (array_is_list($val)) {
                    $val = new Vector($val);
                } else {
                    $val = new Hash($val);
                }
            }

            $env->set($key, $val);
        }

        return self::evalWithEnv($source, $code, $env);
    }

    public static function evalFile(string $file): void
    {
        Log::info("Eval file: $file");
        $code = file_get_contents($file);
        self::$lisp->readEval("(do $code)");
    }

    public static function toString($value, bool $readable): string
    {
        return self::$lisp->pstr($value, $readable);
    }

    private static function evalWithEnv(BaseObject $source, string $code, Env $env)
    {
        try {
            return self::$lisp->readEval($code, $env);
        } catch (\Throwable $ex) {
            $context = [
                'entity' => $source->getTechnicalName(),
                'code' => $code,
            ];

            if ($source instanceof Living || $source instanceof Item) {
                $room = $source->getRoom();
                if ($room) {
                    $context['room'] = $room->getTemplate()->getId();
                }
            }

            Log::error('Lisp: ' . $ex->getMessage(), $context);
            return null;
        }
    }
}
