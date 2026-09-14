<?php
/**
 * Gauntlet MUD - Lisp functions
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use MadLisp\Env;
use MadLisp\Hash;
use MadLisp\Lisp as RealLisp;
use MadLisp\LispFactory;
use MadLisp\Options;
use MadLisp\PhpCompiledProgram;
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
        $options = new Options();
        $options->safemode = true;

        $factory = new LispFactory();
        self::$lisp = $factory->make($options);

        $funcs->register(self::$lisp->getEnv());

        $files = array_merge([
            APP_DIR . 'bootstrap.lisp',
        ], glob(DATA_DIR . 'lisp/*.lisp'));

        foreach ($files as $file) {
            Log::info("Eval file: $file");
            $code = file_get_contents($file);
            self::$lisp->readEvalCompiled("(do $code)");
        }
    }

    public static function compile(string $code): PhpCompiledProgram
    {
        $ast = self::$lisp->read($code);

        return self::$lisp->compile($ast);
    }

    public static function exec(BaseObject $source, PhpCompiledProgram $script)
    {
        $env = $source->createLispEnv(self::$lisp->getEnv());
        return self::execWithEnv($source, $script, $env);
    }

    public static function execWithData(BaseObject $source, PhpCompiledProgram $script, array $data)
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

        return self::execWithEnv($source, $script, $env);
    }

    public static function toString($value, bool $readable): string
    {
        return self::$lisp->pstr($value, $readable);
    }

    private static function execWithEnv(BaseObject $source, PhpCompiledProgram $script, Env $env)
    {
        try {
            return $script->execute($env);
        } catch (\Throwable $ex) {
            $context = [
                'entity' => $source->getTechnicalName(),
                'code' => $script->getSource(),
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
