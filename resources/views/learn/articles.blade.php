@extends('layouts.app')

@section('content')

<div id="panel_learn" class="container">
    <div class="col-sm-12" v-if="showCategory">
      <table class="table">
        <thead>
          <tr>
            <th>
              ID
            </th>
            <th>
              Name
            </th>
            <th>
              Description
            </th>
          </thead>
          <tbody>
          <tr v-for="(article, key) in articles">
            <td>@{{article.id}}</td>
            <td><a href="#" v-on:click.stop="getWritePage(key)">@{{article.name_eng | unescape}}</a></td>
            <td v-html="app.$options.filters.description(article.body_eng)">@{{article.body_eng | description | unescape}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="!showCategory" class="rows">
      <div class="row" style="margin-bottom: 10px;">
        <button class="btn btn-primary col-sm-12" v-on:click="showCategory = !showCategory">Back</button>
      </div>
      <div class="row">
        <div class="col-sm-6">
          <textarea v-model.trim="articleBody" class="edit-wrapper" id=""></textarea>
        </div>
        <div class="col-sm-6" id="example-body" v-html="currentArticle.body_eng">
        </div>
      </div>
    </div>
</div>
<script>
  Vue.filter('unescape', function (value) {
      value = value.replace(/&rsquo;/g, "'");
      value = value.replace(/&nbsp;/g, " ");
      value = value.replace(/&quot;/g, '"');
      return value;
  });
  Vue.filter('description', function(value){
    if(value.length > 100){
      value = value.substr(0, 100) + '...';
    }
    return value;
  });
  var app = new Vue({
      el: '#panel_learn',
      data: {
          articles: [],
          showCategory: true,
          currentArticle: [],
          articleBody: ''
      },
      created: function(){
          fetch('{{ url("/articles") }}')
                  .then((response) => {
                  if (response.ok) {
                  return response.json();
                  }
                  throw new Error('Network response was not ok');
                  })
                  .then((json) => {
                  this.articles = json;
                  })
                  .catch((error) => {
                  console.log(error);
                  });
      },
      watch: {
          articleBody: function(textValue){
              var clearText = this.getClearText(this.currentArticle.body_eng);
              var clearTextValue = this.getClearText(textValue);
              var lenV = clearTextValue.length;
              if(lenV > 0){
                var subClearText = clearText.substr(0,lenV);
                if(subClearText == clearTextValue){
                  this.currentArticle.body_eng = '<strong>'+subClearText+'</strong>'+clearText.substr(lenV);
                } else {
                  //this.currentArticle.body_eng = clearText;
                }
              }
              //console.log(clearText);
          }
      },
      methods: {
          getWritePage: function(id){
              if (typeof (this.articles[id]) !== "undefined"){
                  this.currentArticle = this.articles[id];
                  this.showCategory = false;
                  var b = document.getElementById("example-body");
                  var h = b.clientHeight || b.offsetHeight;
              }
          },
          getClearText: function(textValue){
              /*textValue = textValue.replace(/&rsquo;/g, "'");
              textValue = textValue.replace(/&nbsp;/g, " ");
              textValue = textValue.replace(/&quot;/g, '"');*/
              //textValue = textValue.replace(/ /g, '');
              //todo...
              textValue = textValue.replace(/<p>/g, '');
              textValue = textValue.replace(/<\/p>/g, '');

              textValue = textValue.replace(/<strong>/g, '');
              textValue = textValue.replace(/<\/strong>/g, '');
              return textValue;
          }
      }
  });
</script>
@endsection
