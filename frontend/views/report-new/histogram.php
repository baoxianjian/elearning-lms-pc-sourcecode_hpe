<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

 <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="row">
                    <div id="echarts" style="margin: 10px auto; width:100%; min-height:350px; height:auto;"></div>
                  </div>
                  <table class="table table-bordered table-hover table-striped sortable table-center">
                    <thead>
                      <tr>
                        <!-- asc 或者 desc 表示 升序和降序 -->
                        <th data-defaultsort="asc">月份</th>
                        <th>注册课程</th>
                        <th>完成课程</th>
                        <th>学习时长(分钟)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>1月</td>
                        <td>19</td>
                        <td>10</td>
                        <td>130</td>
                      </tr>
                      <tr>
                        <td>2月</td>
                        <td>11</td>
                        <td>10</td>
                        <td>130</td>
                      </tr>
                      <tr>
                        <td>3月</td>
                        <td>13</td>
                        <td>10</td>
                        <td>130</td>
                      </tr>
                    </tbody>
                  </table>
                  <nav>
                    <ul class="pagination pull-right">
                      <li>
                        <a href="#" aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>
                      <li class="active"><a href="#">1</a></li>
                      <li><a href="#">2</a></li>
                      <li><a href="#">3</a></li>
                      <li><a href="#">4</a></li>
                      <li><a href="#">5</a></li>
                      <li>
                        <a href="#" aria-label="Next">
                          <span aria-hidden="true">&raquo;</span>
                        </a>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>
                             
 <?=Html::cssFile('/static/frontend/css/bootstrap-sortable.css')?>
       <?=Html::jsFile('/static/frontend/js/bootstrap-sortable.js')?>
    <?=Html::jsFile('/static/frontend/js/moment.js')?>
     <?=Html::jsFile('/static/frontend/js/echarts.min.js')?>
   <script type="text/javascript">
  // 基于准备好的dom，初始化echarts实例
  var myChart = echarts.init(document.getElementById('echarts'));

  // 指定图表的配置项和数据
  var option = {
    title: {
      text: 'ECharts 入门示例'
    },
    tooltip: {},
    legend: {
      data: ['销量']
    },
    xAxis: {
      data: ["衬衫", "羊毛衫", "雪纺衫", "裤子", "高跟鞋", "袜子"]
    },
    yAxis: {},
    series: [{
      name: '销量',
      type: 'bar',
      data: [5, 20, 36, 10, 10, 20]
    }]
  };



  // 使用刚指定的配置项和数据显示图表。
  myChart.setOption(option);
  window.onresize = myChart.resize;
  </script> 
    