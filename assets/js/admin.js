/**
 * Wordnest 后台设置页脚本
 * 通过 wp_enqueue_script 加载，替代此前内联的 <script> 标签。
 */
document.addEventListener('DOMContentLoaded', function () {
    // 全选/取消全选功能（仅在术语表格存在时生效）
    var selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            var checkboxes = document.querySelectorAll('input[name="term_ids[]"]');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    }

    // 标签页切换
    var tabs = document.querySelectorAll('.nav-tab');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            // 移除所有标签页的活动类
            tabs.forEach(function (t) {
                t.classList.remove('nav-tab-active');
            });

            // 为点击的标签页添加活动类
            this.classList.add('nav-tab-active');

            // 隐藏所有标签页内容
            var tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(function (content) {
                content.style.display = 'none';
            });

            // 显示选中的标签页内容
            var tabId = this.getAttribute('href');
            var selectedContent = document.querySelector(tabId);
            if (selectedContent) {
                selectedContent.style.display = 'block';
            }
        });
    });
});
