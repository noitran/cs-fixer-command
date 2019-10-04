<?php

return [
    /*
     * Directories to exclude
     */
    'excludes' => [
        'bootstrap/cache',
        'bower_components',
        'node_modules',
        'tasks',
        'public',
        'bin',
        'storage',
        'vendor',
    ],

    /*
     * Default fixer rules
     * List of all fixers can be found here:
     * https://github.com/FriendsOfPHP/PHP-CS-Fixer#usage
     */
    'rules' => [
        // General fixers
        'psr0' => false,
        '@Symfony' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'not_operator_with_successor_space' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ],
        'linebreak_after_opening_tag' => true,
        'method_argument_space' => [
            'keep_multiple_spaces_after_comma' => true,
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'fopen_flags' => true,
        // 'heredoc_indentation' => true, // Can be enabled on PHP >= 7.3 environment
        'list_syntax' => [
            'syntax' => 'short',
        ],
        // @PhpCsFixer:risky
        'logical_operators' => true,
        // @PHP71Migration
        'ternary_to_null_coalescing' => true,
        // @PHP71Migration:risky
        'void_return' => true,
        'random_api_migration' => true,
        'pow_to_exponentiation' => true,
        'declare_strict_types' => true,
        // @Symfony overrides
        'concat_space' => [
            'spacing' => 'one',
        ],
        // @Symfony:risky
        'is_null' => true,
        'modernize_types_casting' => true,
        'dir_constant' => true,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => false,
        ],
        'self_accessor' => true,
        'no_alias_functions' => true,
        'function_to_constant' => true,
        'ereg_to_preg' => true,
        'fopen_flag_order' => true,
        'implode_call' => true,
        // Temporary disabled
        // 'native_function_invocation' => [
        //     'include' => [
        //         '@compiler_optimized',
        //     ],
        //     'scope' => 'namespaced',
        //     'strict' => true,
        // ],
        'php_unit_construct' => true,
        // PHPUnit
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],
        'php_unit_test_annotation' => [
            'style' => 'annotation',
        ],
        // PHPDoc
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_order' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
    ],

    /*
     * Location and name of pre-commit hook file, that
     * will be copied into git directory
     */
    'git_pre_commit_file' => \dirname(__DIR__) . '/_contrib/pre-commit',

    /*
     * Git base path to install pre-commit hook
     *
     * For default projects without docker path should be
     * base_path('.git/hooks'),
     */
    'git_hooks_path' => base_path('../.git/hooks'),
];
