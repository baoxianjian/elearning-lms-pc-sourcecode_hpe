<?php
use frontend\assets\DefaultAppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */

\frontend\assets\AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= Yii::t('system','learn_home_page')?></title>
  <!-- Bootstrap -->
  <?= Html::csrfMetaTags() ?>
  <title><?= Yii::t('system','frontend_name')?></title>
  <?php $this->head() ?>
  <?= Html::cssFile('/vendor/bower/bootstrap/dist/css/bootstrap.min.css')?>
  <?= Html::cssFile('/static/frontend/css/c.segment.css') ?>

  <style type="text/css">
  html {
    color: white;
    background-color: #2883B6;
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUY1RjdCNkVFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUY1RjdCNkZFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RjVGN0I2Q0U5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RjVGN0I2REU5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgQ7RJEAAABkSURBVHjaYvj//z8DEDtDaRAWpwebEWQpAwODIBC/Z4AA+rAHysdMDAMEWIBYHOp9caiYKJI8zdggi19Cw/8lkiTN2aNBPRrUo0E9GtSjQT0a1KNBPRrUo0E9GtSjQU05GyDAAD0qLnnflqfDAAAAAElFTkSuQmCC");
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUY1RjdCNkVFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUY1RjdCNkZFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RjVGN0I2Q0U5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RjVGN0I2REU5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgQ7RJEAAABkSURBVHjaYvj//z8DEDtDaRAWpwebEWQpAwODIBC/Z4AA+rAHysdMDAMEWIBYHOp9caiYKJI8zdggi19Cw/8lkiTN2aNBPRrUo0E9GtSjQT0a1KNBPRrUo0E9GtSjQU05GyDAAD0qLnnflqfDAAAAAElFTkSuQmCC"), -webkit-radial-gradient(center, ellipse cover, #2883b6 0%, #084688 100%);
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUY1RjdCNkVFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUY1RjdCNkZFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RjVGN0I2Q0U5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RjVGN0I2REU5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgQ7RJEAAABkSURBVHjaYvj//z8DEDtDaRAWpwebEWQpAwODIBC/Z4AA+rAHysdMDAMEWIBYHOp9caiYKJI8zdggi19Cw/8lkiTN2aNBPRrUo0E9GtSjQT0a1KNBPRrUo0E9GtSjQU05GyDAAD0qLnnflqfDAAAAAElFTkSuQmCC"), -moz-radial-gradient(center, ellipse cover, #2883b6 0%, #084688 100%);
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUY1RjdCNkVFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUY1RjdCNkZFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RjVGN0I2Q0U5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RjVGN0I2REU5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgQ7RJEAAABkSURBVHjaYvj//z8DEDtDaRAWpwebEWQpAwODIBC/Z4AA+rAHysdMDAMEWIBYHOp9caiYKJI8zdggi19Cw/8lkiTN2aNBPRrUo0E9GtSjQT0a1KNBPRrUo0E9GtSjQU05GyDAAD0qLnnflqfDAAAAAElFTkSuQmCC"), -o-radial-gradient(center, ellipse cover, #2883b6 0%, #084688 100%);
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUY1RjdCNkVFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUY1RjdCNkZFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RjVGN0I2Q0U5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RjVGN0I2REU5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgQ7RJEAAABkSURBVHjaYvj//z8DEDtDaRAWpwebEWQpAwODIBC/Z4AA+rAHysdMDAMEWIBYHOp9caiYKJI8zdggi19Cw/8lkiTN2aNBPRrUo0E9GtSjQT0a1KNBPRrUo0E9GtSjQU05GyDAAD0qLnnflqfDAAAAAElFTkSuQmCC"), -ms-radial-gradient(center, ellipse cover, #2883b6 0%, #084688 100%);
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUY1RjdCNkVFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUY1RjdCNkZFOTkyMTFFMjhGMDdFMEEyQjQ0NDQ1RjYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RjVGN0I2Q0U5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RjVGN0I2REU5OTIxMUUyOEYwN0UwQTJCNDQ0NDVGNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgQ7RJEAAABkSURBVHjaYvj//z8DEDtDaRAWpwebEWQpAwODIBC/Z4AA+rAHysdMDAMEWIBYHOp9caiYKJI8zdggi19Cw/8lkiTN2aNBPRrUo0E9GtSjQT0a1KNBPRrUo0E9GtSjQU05GyDAAD0qLnnflqfDAAAAAElFTkSuQmCC"), radial-gradient(ellipse at center, #2883b6 0%, #084688 100%);
    background-repeat: repeat;
    background-repeat: repeat, no-repeat;
    min-height: 100%;
    min-width: 100%;
  }
  body{
    padding-top: 0 !important;
  }
  .form-control {
    float: left;
    margin-bottom: 10px;
  }
  #sorecord-title{
    margin: 0;
    border-bottom: 1px dotted #eee;
    border-radius: 0;
    width:100%;
  }
  #sorecord-content {
    border-radius: 0;
    border-top: none;
    height:70px;
    width:100%;
  }
  #sorecord-url {
    width:100%;
  }
  .timeScope {
    margin-top: 0;
    margin-right: 2rem;
  }
  .shareInput .btn-success {
    padding: 5px 35px;
  }
  .btn-success {
    background: #00993a;
  }
  /* 我要记录表单css */
  .field-sorecord-title .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-title .form-control:focus{
    border: 1px solid #CCC;
    border-top: 1px solid #66afe9;
    box-shadow:none;
  }
  .field-sorecord-title .has-success .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-title .has-success .form-control:focus{
    border: 1px solid #CCC;
    border-top: 1px solid #66afe9;
    -webkit-box-shadow:none;
    box-shadow:none;
  }
  .field-sorecord-title .has-error .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-title .has-error .form-control:focus{
    border: 1px solid #CCC;
    border-top: 1px solid #66afe9;
    -webkit-box-shadow:none;
    box-shadow:none;
  }
  .field-sorecord-content .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-content .form-control:focus{
    border: 1px solid #CCC;
    border-bottom: 1px solid #66afe9;
    box-shadow:none;
  }
  .field-sorecord-content .has-success .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-content .has-success .form-control:focus{
    border: 1px solid #CCC;
    border-bottom: 1px solid #66afe9;
    -webkit-box-shadow:none;
    box-shadow:none;
  }
  .field-sorecord-content .has-error .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-content .has-error .form-control:focus{
    border: 1px solid #CCC;
    border-bottom: 1px solid #66afe9;
    -webkit-box-shadow:none;
    box-shadow:none;
  }

  .field-sorecord-url .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-url .form-control:focus{
    border: 1px solid #CCC;
    border-bottom: 1px solid #66afe9;
    box-shadow:none;
  }
  .field-sorecord-url .has-success .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-url .has-success .form-control:focus{
    border: 1px solid #CCC;
    border-bottom: 1px solid #66afe9;
    -webkit-box-shadow:none;
    box-shadow:none;
  }
  .field-sorecord-url .has-error .form-control{
    border: 1px solid #CCC;
    box-shadow:none;
  }
  .field-sorecord-url .has-error .form-control:focus{
    border: 1px solid #CCC;
    border-bottom: 1px solid #66afe9;
    -webkit-box-shadow:none;
    box-shadow:none;
  }
  .panel-body{
    padding:0 15px!important;
  }
  #appMsg {
    display: none;
    position: fixed;
    z-index: 10000;
    height: 46px;
    margin: auto;
    white-space: nowrap;
  }
  .showAppMsg {
    top: -260px;
    bottom: 0;
    left: 0;
    right: 0;
  }
  .glyphicon {
    color: #00609d
  }
  .glyphicon {
    position: relative;
    top: 1px;
    display: inline-block;
    font-family: 'Glyphicons Halflings';
    font-style: normal;
    font-weight: normal;
    line-height: 1;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  </style>
</head>
<body class="modal-open">
  <!-- 记录网页弹出界面 -->
  <div class="modal fade in" id="iWantShare" tabindex="-1" role="dialog" aria-labelledby="iWantShare" aria-hidden="false" style="display: block; padding-right: 15px;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','web_page')?></h4>
        </div>
        <div class="modal-body">
          <div role="tabpanel" class="tab-pane  panel-body" id="motion">
            <form id="recordWebForm" class="shareInput" action="/student/index-tab-web.html" method="post">
              <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
              <input type="hidden" id="is_share" name="is_share" value="0">
              <h5><?=Yii::t('frontend','what_{value}_need',['value'=>Yii::t('frontend','web_page')])?></h5>
              <input type="hidden" id="sorecord-duration" class="form-control" name="SoRecord[duration]" value="">
              <input type="hidden" id="sorecord-attach_original_filename" class="form-control" name="SoRecord[attach_original_filename]" value="">
              <input type="hidden" id="sorecord-attach_url" class="form-control" name="SoRecord[attach_url]" value="">
              <input type="hidden" id="sorecord-record_type" class="form-control" name="SoRecord[record_type]" value="0">
              <div class="form-group field-sorecord-title required has-error">
                <input type="text" id="sorecord-title" class="form-control" name="SoRecord[title]" maxlength="100" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>" value="<?=$title?>">
              </div>
              <div class="form-group field-sorecord-content required">
                <textarea id="sorecord-content" class="form-control" name="SoRecord[content]" placeholder="<?=Yii::t('frontend','record_need')?>"></textarea>
              </div>
              <div class="form-group field-sorecord-url required">
                <input type="text" id="sorecord-url" class="form-control" name="SoRecord[url]" maxlength="225" placeholder="<?=Yii::t('frontend','related_link')?> eg:http://..."  value="<?=$url?>" readonly>
              </div>
              <div class="btn-group timeScope pull-left">
                <button id="btn_dropdown" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"><?=Yii::t('frontend','duration_time')?> &nbsp;<span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,5)">5<?=Yii::t('common','time_minute')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,10)">10<?=Yii::t('common','time_minute')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,30)">30<?=Yii::t('common','time_minute')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,60)">1<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,120)">2<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,180)">3<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,240)">4<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,300)">5<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,360)">6<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,420)">7<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,480)">8<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,540)">9<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,600)">10<?=Yii::t('common','time_hour')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,720)">0.5<?=Yii::t('common','time_day')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,1440)">1<?=Yii::t('common','time_day')?></a></li>
                  <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,2880)">2<?=Yii::t('common','time_day')?></a></li>
                </ul>
              </div>
              <a id="web_upload" href="javascript:void(0);" style="max-width: 25%" class="btn btn-sm btn-default lessWord"><?=Yii::t('frontend','enclosure')?></a>
              <span class="upload-info" style="color:#008000;margin-left:5px;"></span>
                    <span>
                        <?=
                        Html::button(Yii::t('common', 'save'),
                            ['id' => 'saveBtn', 'class' => 'btn btn-success pull-right','onclick'=>'submitNoShare("recordWebForm");'])
                        ?>
                        <?=
                        Html::button(Yii::t('frontend', 'save_share'),
                            ['id' => 'saveShareBtn', 'class' => 'btn btn-default pull-right','style'=>'margin-right:15px;','onclick'=>'submitAndShare("recordWebForm");'])
                        ?>
                    </span>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade in"></div>
  <!-- /container -->
  <?= Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js') ?>
  <?= Html::jsFile('/vendor/bower/jquery-ui/jquery-ui.min.js') ?>
  <?= Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js') ?>
  <?= Html::jsFile('/static/common/js/common.js') ?>
  <?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
  <script>
    var app = [];
    app.showMsg = function (msg, _timeout) {
      var $this = $("#appMsg")
          , THIS = this
          , elWith = $this.removeClass('showAppMsg').css("width", "auto").html('<i class="glyphicon glyphicon-info-sign"></i> ' + msg).width()
          ;
      $this.css("width", elWith + 40 + "px").addClass('showAppMsg').fadeIn("normal", function () {
            THIS._showMsgTimeout = setTimeout(THIS.hideMsg, _timeout || 3000, $this);
            $this.one("click", closeMsg);
          }
      );
      function closeMsg() {
        clearTimeout(THIS._showMsgTimeout);
        $this.fadeOut();
      }

      clearTimeout(THIS._showMsgTimeout);
    };

    app.hideMsg = function ($this) {
      if ("object" == typeof $this) {
        $this.unbind("click").fadeOut();
      }
      clearTimeout(app._showMsgTimeout);
    };

    $("#recordWebForm").on("submit", function (event) {
      event.preventDefault();
      var url = $("#recordWebForm #sorecord-url").val().trim();

      var title = $("#recordWebForm #sorecord-title").val().trim();
      var content = $("#recordWebForm #sorecord-content").val().trim();

      if (title == '') {
        $("#recordWebForm #saveBtn").removeAttr("disabled");
        $("#recordWebForm #saveShareBtn").removeAttr("disabled");

        $("#recordWebForm #sorecord-title").focus();
        app.showMsg("<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>", 1500);
        return false;
      }
      if (content == '') {
        $("#recordWebForm #saveBtn").removeAttr("disabled");
        $("#recordWebForm #saveShareBtn").removeAttr("disabled");

        $("#recordWebForm #sorecord-content").focus();
        app.showMsg("<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','record_content')])?>", 1500);
        return false;
      }
      if (url == '') {
        $("#recordWebForm #saveBtn").removeAttr("disabled");
        $("#recordWebForm #saveShareBtn").removeAttr("disabled");

        $("#recordWebForm #sorecord-url").focus();
        app.showMsg("<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','related_link')])?>", 1500);
        return false;
      }

      if (url != '') {
        var strRegex = '^((https|http|ftp|rtsp|mms)?://)'
            + '?(([0-9a-z_!~*\'().&=+$%-]+: )?[0-9a-z_!~*\'().&=+$%-]+@)?' //ftp的user@
            + '(([0-9]{1,3}.){3}[0-9]{1,3}' // IP形式的URL- 199.194.52.184
            + '|' // 允许IP和DOMAIN（域名）
            + '([0-9a-z_!~*\'()-]+.)*' // 域名- www.
            + '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z].' // 二级域名
            + '[a-z]{2,6})' // first level domain- .com or .museum
            + '(:[0-9]{1,4})?' // 端口- :80
            + '((/?)|' // a slash isn't required if there is no file name
            + '(/[0-9a-z_!~*\'().;?:@&=+$,%#-]+)+/?)$';
        var pattern = new RegExp(strRegex);
        if (!pattern.test(url.toLowerCase())) {
          $("#recordWebForm #saveBtn").removeAttr("disabled");
          $("#recordWebForm #saveShareBtn").removeAttr("disabled");

          $("#recordWebForm #sorecord-url").focus();
          app.showMsg("<?=Yii::t('frontend','invalid_url')?>", 1500);
          return false;
        }
      }

      submitModalForm("", "recordWebForm", "", true, false, null, null);
    });

    var ajaxUploadUrl = "<?=Url::toRoute(['student/upload'])?>";
    //异步上传文件
    new AjaxUpload("#web_upload", {
      action: ajaxUploadUrl,
      type: "POST",
      name: 'myfile',
      data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
      onSubmit: function (file, ext) {
        $("#recordWebForm #saveBtn").attr({"disabled": "disabled"});
        $("#recordWebForm #saveShareBtn").attr({"disabled": "disabled"});
        $("#recordWebForm .upload-info").html("<?=Yii::t('common', 'uploading')?>");
      },
      onComplete: function (file, response) {
        var result = JSON.parse(response);

        if (result.info == "<?=Yii::t('common', 'file_type_error')?>" || result.info == "<?=Yii::t('common', 'upload_error')?>") {
          $("#recordWebForm .upload-info").html(result.info);
        }
        else {
          //生成元素
          $("#web_upload").html(result.filename);
          $('div:last').attr('title', result.filename);

          //传递参数上传
          $("#recordWebForm #sorecord-attach_original_filename").val(result.filename);
          $("#recordWebForm #sorecord-attach_url").val(result.info);
          //更新提示信息
          $("#recordWebForm .upload-info").html("<?=Yii::t('common', 'upload_completed')?>");
        }
        $("#recordWebForm #saveBtn").removeAttr("disabled");
        $("#recordWebForm #saveShareBtn").removeAttr("disabled");
      }
    });

    function selectDuration(formId, obj, duration) {
      $("#" + formId + " #btn_dropdown").html($(obj).html() + ' &nbsp;<span class="caret">');
      $("#" + formId + " #sorecord-duration").val(duration);
    }

    function submitNoShare(formId) {
      $("#" + formId + " #saveBtn").attr({"disabled": "disabled"});
      $("#" + formId + " #saveShareBtn").attr({"disabled": "disabled"});
      $("#" + formId + " #is_share").val("0");
      $("#" + formId).submit();
    }

    function submitAndShare(formId) {
      $("#" + formId + " #saveBtn").attr({"disabled": "disabled"});
      $("#" + formId + " #saveShareBtn").attr({"disabled": "disabled"});
      $("#" + formId + " #is_share").val("1");
      $("#" + formId).submit();
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose) {
      app.showMsg('<?= Yii::t('common', 'operation_success') ?>', 1500);
      window.close();
    }
  </script>
  <div id="appMsg" class="ui raised segment"></div>
</body>
</html>
<?php $this->endPage() ?>