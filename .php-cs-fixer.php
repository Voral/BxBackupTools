<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

return
    (new PhpCsFixer\Config())
        ->setFinder($finder)
        ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
        ->setRiskyAllowed(true)
        ->setRules([
            '@PHP80Migration:risky' => true,
            '@PHP81Migration' => true,
            '@PhpCsFixer' => true,
            '@PhpCsFixer:risky' => true,
            '@PHPUnit84Migration:risky' => true,
            '@PSR12' => true,
            '@PSR12:risky' => true,
            'blank_line_before_statement' => [
                'statements' => [
                    'continue',
                    'declare',
                    'default',
                    'return',
                    'throw',
                    'try',
                ],
            ],
            'blank_line_between_import_groups' => false,
            'braces' => [
                'allow_single_line_anonymous_class_with_empty_body' => true,
                'allow_single_line_closure' => true,
            ],
            'comment_to_phpdoc' => ['ignored_tags' => ['fixme']],
            'date_time_immutable' => true,
            'final_class' => false,
            'final_public_method_for_abstract_class' => true,
            'fopen_flags' => ['b_mode' => true],
            'global_namespace_import' => [
                'import_constants' => false,
                'import_functions' => false,
                'import_classes' => false,
            ],
            'method_chaining_indentation' => false,
            'no_break_comment' => false,
            'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
            'no_trailing_whitespace_in_string' => false,
            'nullable_type_declaration_for_default_null_value' => true,
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'case',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public_static',
                    'property_protected_static',
                    'property_private_static',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'construct',
                    'destruct',
                    'phpunit',
                    'method_public_static',
                    'method_public_abstract_static',
                    'method_protected_static',
                    'method_protected_abstract_static',
                    'method_private_static',
                    'method_public',
                    'method_public_abstract',
                    'method_protected',
                    'method_protected_abstract',
                    'method_private',
                ],
            ],
            'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
            'php_unit_strict' => false,
            'php_unit_test_case_static_method_calls' => false,
            'phpdoc_add_missing_param_annotation' => false,
            'phpdoc_align' => false,
            'phpdoc_separation' => false,
            'phpdoc_to_comment' => false,
            'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
            'return_assignment' => false,
            'single_line_comment_style' => ['comment_types' => ['hash']],
            'trailing_comma_in_multiline' => [
                'after_heredoc' => true,
                'elements' => ['arrays', 'arguments', 'parameters']
            ],
            'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
            'array_syntax' => ['syntax' => 'short'],
            'native_function_invocation' => false,
            'concat_space' => ['spacing' => 'one'],
        ]);
