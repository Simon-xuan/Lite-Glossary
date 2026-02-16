<?php
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 过滤内容，为词汇表术语添加工具提示
 */
function lite_glossary_filter_content( $content ) {
    // 仅在单个文章/页面过滤
    if ( ! is_singular() ) {
        return $content;
    }
    
    // 获取词汇表术语（带缓存）
    $terms = lite_glossary_get_terms();
    
    if ( empty( $terms ) ) {
        return $content;
    }
    
    // 获取插件设置
    $first_occurrence_only = get_option( 'lite_glossary_first_occurrence_only', false );
    
    // 跟踪已找到的术语（仅首次出现选项）
    $found_terms = array();
    
    // 使用 DOMDocument 解析内容，避免修改链接和标题
    $dom = new DOMDocument();
    
    // 添加文档类型以避免解析问题
    $content = '<!DOCTYPE html><html><body>' . $content . '</body></html>';
    
    // 抑制格式错误的 HTML 警告
    @$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
    
    // 获取所有文本节点
    $text_nodes = lite_glossary_get_text_nodes( $dom->getElementsByTagName( 'body' )->item( 0 ) );
    
    // 处理每个文本节点
    foreach ( $text_nodes as $node ) {
        $text = $node->nodeValue;
        $modified_text = $text;
        
        // 处理每个术语
        foreach ( $terms as $term ) {
            $term_title = $term['title'];
            $term_content = $term['content'];
            
            // 如果仅首次出现且术语已找到，则跳过
            if ( $first_occurrence_only && in_array( $term_title, $found_terms ) ) {
                continue;
            }
            
            // 创建正则表达式模式，支持中文和标点符号的词边界
            // 确保术语不被英文字母包围
            $pattern = '/(?<![a-zA-Z])(' . preg_quote( $term_title, '/' ) . ')(?![a-zA-Z])/u';
            
            // 用工具提示替换术语
            $replacement = '<span class="lite-glossary-term" data-tooltip="' . esc_attr( wp_strip_all_tags( $term_content ) ) . '">$1</span>';
            
            // 如果启用了仅首次出现，则只替换第一个匹配项
            if ( $first_occurrence_only ) {
                $count = 0;
                $modified_text = preg_replace( $pattern, $replacement, $modified_text, 1, $count );
                
                // 如果进行了替换，将术语添加到已找到列表
                if ( $count > 0 ) {
                    $found_terms[] = $term_title;
                }
            } else {
                // 替换所有匹配项
                $modified_text = preg_replace( $pattern, $replacement, $modified_text );
            }
        }
        
        // 用修改后的文本替换原始文本节点
        if ( $modified_text !== $text ) {
            $new_node = $dom->createDocumentFragment();
            $new_node->appendXML( $modified_text );
            $node->parentNode->replaceChild( $new_node, $node );
        }
    }
    
    // 获取修改后的内容
    $body = $dom->getElementsByTagName( 'body' )->item( 0 );
    $modified_content = '';
    
    if ( $body ) {
        foreach ( $body->childNodes as $child ) {
            $modified_content .= $dom->saveHTML( $child );
        }
    }
    
    return $modified_content;
}
add_filter( 'the_content', 'lite_glossary_filter_content' );

/**
 * 获取词汇表术语（带缓存）
 */
function lite_glossary_get_terms() {
    // 检查瞬态缓存
    $terms = get_transient( 'lite_glossary_terms' );
    
    if ( false === $terms ) {
        // 查询数据库获取词汇表术语
        $args = array(
            'post_type'      => 'glossary',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids', // 仅获取文章 ID 以提高性能
        );
        
        $post_ids = get_posts( $args );
        
        $terms = array();
        
        if ( ! empty( $post_ids ) ) {
            foreach ( $post_ids as $post_id ) {
                $term_title = get_the_title( $post_id );
                $term_content = get_post_field( 'post_content', $post_id );
                
                // 分割多个术语（格式：术语1｜术语2）
                $terms_list = explode( '｜', $term_title );
                
                foreach ( $terms_list as $single_term ) {
                    $single_term = trim( $single_term );
                    if ( ! empty( $single_term ) ) {
                        // 处理换行过多的问题
                        $clean_content = wp_strip_all_tags( $term_content );
                        $clean_content = preg_replace( '/\s+/', ' ', $clean_content ); // 将多个连续空白字符替换为单个空格
                        
                        $terms[] = array(
                            'title'   => $single_term,
                            'content' => $clean_content,
                        );
                    }
                }
            }
        }
        
        // 存储在瞬态缓存中，有效期 1 小时
        set_transient( 'lite_glossary_terms', $terms, HOUR_IN_SECONDS );
    }
    
    return $terms;
}

/**
 * 当词汇表文章更新时清除缓存
 */
function lite_glossary_clear_transient( $post_id ) {
    if ( get_post_type( $post_id ) === 'glossary' ) {
        delete_transient( 'lite_glossary_terms' );
    }
}
add_action( 'save_post', 'lite_glossary_clear_transient' );
add_action( 'delete_post', 'lite_glossary_clear_transient' );

/**
 * 递归获取所有文本节点，排除链接和标题
 */
function lite_glossary_get_text_nodes( $node, &$text_nodes = array() ) {
    // 跳过链接和标题
    if ( in_array( $node->nodeName, array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ) {
        return $text_nodes;
    }
    
    // 处理子节点
    if ( $node->hasChildNodes() ) {
        foreach ( $node->childNodes as $child ) {
            if ( $child->nodeType === XML_TEXT_NODE && trim( $child->nodeValue ) !== '' ) {
                $text_nodes[] = $child;
            } else {
                lite_glossary_get_text_nodes( $child, $text_nodes );
            }
        }
    }
    
    return $text_nodes;
}

/**
 * 注册插件设置
 */
function lite_glossary_register_settings() {
    add_option( 'lite_glossary_first_occurrence_only', false );
    register_setting( 'lite_glossary_settings', 'lite_glossary_first_occurrence_only' );
}
add_action( 'admin_init', 'lite_glossary_register_settings' );