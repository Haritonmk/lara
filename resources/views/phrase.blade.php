@extends('layouts.app')

@section('content')
<script src="<?=asset('js/vue.min.js');?>"></script>
<script src="<?=asset('js/vue-resource.js');?>"></script>
<div id="panel_learn" class="container">
    <div class="col-sm-6">
        <div id="phrase">@{{phrase}}</div>
        <textarea v-model.trim="translatePhrase"></textarea><br />
        <button @click="newPhrase()" class="btn btn-primary">New Phrase</button>
    </div>
    <div class="col-sm-6">
        <div v-html="this.$options.filters.correct(translatePhrase,correctTranslate)">@{{translatePhrase | correct:correctTranslate}}</div>
    </div>
</div>
<script>
    Vue.filter('correct',function(value,correct){
        var lenV = value.length;
        var subCorrect = correct.substr(0,lenV);
        var classC = 'red';
        if(value.toLowerCase() == subCorrect.toLowerCase()){
            classC = 'green';
        }
        return '<span class="'+classC+'">'+value+'</span>';
    });
    var app = new Vue({
        el: '#panel_learn',
        data: {
            phrase: '',
            translatePhrase: '',
            correctTranslate: ''
        },
        created: function (){
           this.newPhrase(); 
        },
        methods: {
            newPhrase: function(){
                this.translatePhrase = '';
                this.$http.get('{{ url("/words/phrase") }}').then(response => {
                    if (response.ok) {
                        this.phrase = response.body.qrus;
                        var qeng = response.body.qeng
                        this.correctTranslate = qeng.trim();
                    }
                  }, response => {
                    console.log(response);
                  });
            }
        }
    });
</script>
<style>
    .red {
        color: #dd0000;
    }
    .green {
        color: #009926;
    }
</style>
@endsection