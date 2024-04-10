<?php

use PhpCsFixer\Config;

$config = new Config();

$config->getFinder()->in([
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);

return $config
    ->setRules([
        '@PSR12' => true,
        '@PHP80Migration' => true,
        'no_unused_imports' => true,
        'declare_strict_types' => true,
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => true,
        ],
        'self_accessor' => true,
        'self_static_accessor' => true,
        'no_unneeded_braces' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'yoda_style' => true,
        'type_declaration_spaces' => true,
        'lambda_not_used_import' => true,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_parentheses' => true,
        'explicit_indirect_variable' => true,
        'single_space_around_construct' => true,
        'no_leading_namespace_whitespace' => true,
        'concat_space' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => true,
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'align_multiline_comment' => [
            'comment_type' => 'all_multiline',
        ],
        'general_phpdoc_tag_rename' => [
            'replacements' => [
                'inheritDocs' => 'inheritDoc',
            ],
        ],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_align' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_single_line_var_spacing' => true,
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,
        'explicit_string_variable' => true,
        'single_quote' => true,
        'array_indentation' => true,
        'blank_line_before_statement' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => true,
        'no_spaces_around_offset' => true,
        'types_spaces' => true,
    ])
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/.php_cs.cache');