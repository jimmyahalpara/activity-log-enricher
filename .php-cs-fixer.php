<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->in(__DIR__ . '/config')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        
        // Array
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'trim_array_spaces' => true,
        
        // Binary operators
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => ['=>' => null],
        ],
        
        // Blank lines
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'if', 'foreach', 'for', 'while'],
        ],
        'blank_line_after_opening_tag' => true,
        
        // Casing
        'constant_case' => ['case' => 'lower'],
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        
        // Cast notation
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'short_scalar_cast' => true,
        
        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => true,
            'single_item_single_line' => true,
            'single_line' => true,
        ],
        
        // Comments
        'comment_to_phpdoc' => true,
        'multiline_comment_opening_closing' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        
        // Control structure
        'include' => true,
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_unneeded_control_parentheses' => true,
        'no_useless_else' => true,
        'switch_continue_to_break' => true,
        'yoda_style' => false,
        
        // Function notation
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'type_declaration_spaces' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
        'no_spaces_after_function_name' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        
        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        
        // Language construct
        'declare_equal_normalize' => ['space' => 'none'],
        'declare_strict_types' => true,
        
        // Namespace
        'blank_line_after_namespace' => true,
        'no_leading_namespace_whitespace' => true,
        
        // Operator
        'concat_space' => ['spacing' => 'one'],
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        
        // PHP tag
        'echo_tag_syntax' => ['format' => 'long'],
        'linebreak_after_opening_tag' => true,
        'no_closing_tag' => true,
        
        // PHPDoc
        'general_phpdoc_annotation_remove' => ['annotations' => ['author', 'package']],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_type' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        
        // Return notation
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,
        
        // Semicolon
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'space_after_semicolon' => true,
        
        // Strict
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        
        // String notation
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'simple_to_complex_string_variable' => true,
        'single_quote' => true,
        
        // Whitespace
        'array_indentation' => true,
        'compact_nullable_type_declaration' => true,
        'heredoc_indentation' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'extra',
                'return',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block',
            ],
        ],
        'no_spaces_around_offset' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'types_spaces' => true,
        
        // Disable conflicting rules
        'single_line_throw' => false,
        'multiline_whitespace_before_semicolons' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
    ])
    ->setFinder($finder);