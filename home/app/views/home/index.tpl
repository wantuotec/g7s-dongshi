<div class="banner">
    <section class="box">
        <ul class="texts">
          <p>生活会是什么颜色</p>
          <p>只管举杯执剑天涯</p>
          <p>以梦为马,诗酒乘年华...</p>
        </ul>
    </section>
</div>

<!-- 首页垂直滚动公告 -->
<div class="website-news-box">
    <div class="website-news-img">站点公告</div>
    <ul class="website-news">
        <?php if (!empty($list['website_notice'])) : 
            foreach ($list['website_notice'] as $notice) :
        ?>
            <li><?php echo $notice['content'];?></li>
        <?php endforeach;endif;?>
    </ul>
</div>

<div class="dynamic">
    <div class="box">
        <h3><p>最新动态<img src="<?php echo HOME_DOMAIN;?>public/images/mess_03.gif" alt=""></p></h3>
        <ul>
            <?php if (!empty($list['new_info'])) : 
                foreach ($list['new_info'] as $news) :
            ?>
                <li><a href="<?php echo $news['href'];?>" title="<?php echo $news['desc'];?>" ><span class="new-title">【<?php echo $news['title'];?>】</span><span class="new-cont"><?php echo $news['desc'];?><span></a><span class="new-time"><?php echo $news['date'];?></span></li>
            <?php endforeach;endif;?>
        </ul>
    </div>
</div>

<article>
    <div class="title_tj">
        <h3><p>文章推荐</p></h3>
        <img src="<?php echo HOME_DOMAIN;?>public/images/recommend01.png" alt="">
    </div>

    <!--生活文章推荐模块 START-->
    <div class="article_left">
        <?php if (!empty($list['recommend_article'])) : 
            foreach ($list['recommend_article'] as $article) : 
        ?>
            <div class="article_tj">
                <h3><a href="<?php echo $article['href'];?>" ><?php echo $article['article_title'];?></a></h3>
                <figure><img src="<?php echo $article['cover_photo'];?>" alt="<?php echo $article['article_title'];?>"></figure>
                <p><?php echo $article['cover_words'];?></p>
                <div class="clear_both"></div>
                <div class="tj_date_view">
                    <span class="date"><?php echo $article['date'];?> </span>
                    <span class="origin"><?php echo $article['origin_type_title'];?> </span>
                    <span class="browse"><?php echo $article['read_num'];?> </span>
                    <span class="like"><?php echo $article['like_num'];?> </span>
                    <a href="<?php echo $article['href'];?>" class="readmore">阅读全文>></a>
                </div>
            </div>
        <?php endforeach;endif; ?>
    </div>
    <!--文章推荐模块 END-->

    <!--这里是右边部分，顶格-->
    <div class="article_right">
        <!--天气模块 START-->
        <div class="weather"><iframe width="300" scrolling="no" height="100" frameborder="0" allowtransparency="true" src="http://i.tianqi.com/index.php?c=code&id=48&icon=1&num=1"></iframe></div>
        <!--天气模块 END-->

        <!--日历模块 START-->
        <div id="CalendarMain">
            <div id="title"><a class="selectBtn month" href="javascript:" onclick="CalendarHandler.CalculateLastMonthDays();"><</a><a class="selectBtn selectYear" href="javascript:" onClick="CalendarHandler.CreateSelectYear(CalendarHandler.showYearStart);">2017年</a><a class="selectBtn selectMonth" onClick="CalendarHandler.CreateSelectMonth()">1月</a> <a class="selectBtn nextMonth" href="javascript:" onClick="CalendarHandler.CalculateNextMonthDays();">></a><a class="selectBtn currentDay" href="javascript:" onClick="CalendarHandler.CreateCurrentCalendar(0,0,0);">今天</a></div>
            <div id="context">
                <div class="week"><h3>一</h3><h3>二</h3><h3>三</h3><h3>四</h3><h3>五</h3><h3>六</h3><h3>日</h3></div>
                <div id="center">
                    <div id="centerMain">
                        <div id="selectYearDiv"></div>
                        <div id="centerCalendarMain">
                            <div id="Container"></div>
                        </div>
                        <div id="selectMonthDiv"></div>
                    </div>
                </div>
                <div id="foots"><a id="footNow">23:59:59</a><span id="footWord">愿时光如你美丽...</span></div>
            </div>
        </div>
        <!--日历模块 END-->

        <!--站点来访统计模块 START-->
        <div class="web_visit">
            <h3 class="visit_title"><span>小站来访</span></h3>
            <script type="text/javascript">
              var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1261866794'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s95.cnzz.com/z_stat.php%3Fid%3D1261866794%26online%3D2' type='text/javascript'%3E%3C/script%3E"));
            </script>
        </div>
        <!--站点来访统计模块 END-->
    </div>
</article>

<script>
    window.onload=function(){
      $(function () {
          var _wrap     = $('.website-news');//定义滚动区域
          var _interval = 3000;           //定义滚动间隙时间
          var _moving;                    //需要清除的动画

          _wrap.hover(function () {
              clearInterval(_moving);//当鼠标在滚动区域中时,停止滚动
          }, function () {
              //内容条目数大于1条时，才去触发
              if ($(".website-news li").length > 1) {
                  _moving = setInterval(function () {
                      var _field = _wrap.find('li:first');//此变量不可放置于函数起始处，li:first取值是变化的
                      var _h     = _field.height();       //取得每次滚动高度
                      _field.animate({ marginTop: -_h + 'px' }, 500, function () {
                          //通过取负margin值，隐藏第一行
                          //隐藏后，将该行的margin值置零，并插入到最后，实现无缝滚动
                          _field.css('marginTop', 0).appendTo(_wrap);
                      })
                  }, _interval)//滚动间隔时间取决于_interval
              }
          }).trigger('mouseleave');//函数载入时，模拟执行mouseleave，即自动滚动

          //内容条目数小于等于1条时，不滚动
          if ($(".website-news li").length <= 1) {
              clearInterval(_moving);
          }
      });
    };
</script>