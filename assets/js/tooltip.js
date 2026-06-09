/**
 * 轻量级词汇表工具提示脚本
 * 使用原生 JavaScript 实现悬停功能
 */

/**
 * 格式化文本
 * @param {string} text - 需要格式化的文本
 * @returns {string} 格式化后的文本
 */
function formatText(text) {
    // 检查是否有标点符号
    var hasPunctuation = /[，。！？；：,.;:!?]/.test(text);
    var result = '';
    
    if (hasPunctuation) {
        // 有标点符号，在每个标点符号后换行，但如果不超过5个字符不换行
        var lastIndex = 0;
        for (var i = 0; i < text.length; i++) {
            var char = text[i];
            if (/[，。！？；：,.;:!?]/.test(char)) {
                var segment = text.substring(lastIndex, i + 1);
                if (segment.length > 5) {
                    result += segment + '<br>';
                    lastIndex = i + 1;
                }
            }
        }
        // 添加剩余部分
        if (lastIndex < text.length) {
            result += text.substring(lastIndex);
        }
    } else {
        // 没有标点符号，检查字数
        if (text.length > 7) {
            // 字数超过7，每5个字符换行
            for (var i = 0; i < text.length; i += 5) {
                result += text.substring(i, i + 5) + '<br>';
            }
        } else {
            // 字数不超过7，不换行
            result = text;
        }
    }
    
    return result;
}

/**
 * 动态调整工具提示高度
 * @param {HTMLElement} tooltip - 工具提示元素
 * @param {string} content - 工具提示内容
 */
function adjustTooltipHeight(tooltip, content) {
    // 固定宽度，高度由内容自动撑开，避免文字被上下裁切
    var fixedWidth = 120; // 固定宽度
    tooltip.style.maxWidth = fixedWidth + 'px';
    tooltip.style.width = fixedWidth + 'px';
    tooltip.style.height = 'auto';
}

document.addEventListener('DOMContentLoaded', function() {
    // 获取所有词汇表术语
    var glossaryTerms = document.querySelectorAll('.lite-glossary-term');
    
    // 处理每个术语
    glossaryTerms.forEach(function(term) {
        // 从数据属性获取工具提示内容
        var tooltipContent = term.getAttribute('data-tooltip');
        
        if (tooltipContent) {
            // 创建工具提示元素
            var tooltip = document.createElement('span');
            tooltip.className = 'lite-glossary-tooltip';
            
            // 格式化文本，每5个字符插入一个换行标签
            tooltip.innerHTML = formatText(tooltipContent);
            
            // 将工具提示作为术语的子元素，使其相对术语（position:relative）定位在正上方
            term.appendChild(tooltip);
            
            // 动态调整工具提示高度
            adjustTooltipHeight(tooltip, tooltipContent);
        }
    });
});