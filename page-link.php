<?php

/**
 * Template Page of Friendship Links
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<article class="main-content page-page">
    <div class="post-header">
        <div class="post-title" itemprop="name headline">
            <?php $this->title() ?>
        </div>
    </div>
    <div id="post-content" class="post-content">
        <?php parseContent($this); ?>
    </div>
</article>
<script>
    var favicon_api = "https://api.dc24.top/favicon.php?url=";
    document.querySelectorAll('div.links').forEach(
        function(links) {
            let str = '<div class="post-lists"><div class="post-lists-body">';
            const bgs = ["grey", "deepgrey", "blue", "purple", "green", "yellow", "red", "orange"];
            links.querySelectorAll("a").forEach(a => {
                // 随机背景色
                let colorClass = bgs[Math.floor(Math.random() * bgs.length)];
                let favicon_img = favicon_api + a.href;
                str += `
                    <div class="post-list-item">
                        <div class="post-list-item-container ">
                            <div class="item-label bg-${colorClass}">
                                <div class="item-title"><a href="${a.href}" target="_blank"
                                        rel="external nofollow ugc">${a.text}</a></div>
                                <div class="item-meta clearfix">
                                    <div class="item-meta-ico bg-ico-book"
                                        style="background: url(${favicon_img}) no-repeat;background-size: 40px auto;border-radius: 30%;">
                                    </div>
                                    <div class="item-meta-date">${new URL(a.href).hostname}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
            });
            links.outerHTML = str;
        });
</script>

<?php $this->need('footer.php'); ?>