<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_namespace' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'one',
        ],
        'indentation_type' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'statement_indentation' => true,
        'function_typehint_space' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
    ])
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder); 