@extends('layouts.app')

@section('content')
<script src="<?=asset('js/vue.min.js');?>"></script>
<script src="<?=asset('js/vue-resource.js');?>"></script>
<div id="panel_learn" class="container">
    <div class="col-sm-6">
        <div id="phrase">@{{phrase}}</div>
        <textarea v-model.trim="translatePhrase" style="width: 100%;"></textarea><br /><br />
        <button @click="newPhrase()" class="btn btn-primary">New Phrase</button>
        <button @click="viewPhrase()" class="btn btn-warning">View Phrase</button>
        <div v-show="open"><strong>@{{insideCorrectTranslate}}</strong>@{{outsideCorrectTranslate}}</div>
    </div>
    <div class="col-sm-6">
        <div v-html="this.$options.filters.correct(translatePhrase,correctTranslate)">@{{translatePhrase | correct:correctTranslate}}</div>
    </div>
</div>
<script>
    Vue.filter('correct',function(value,correct){
        var lenV = value.length;
        if(lenV > 0){
            var subCorrect = correct.substr(0,lenV);
            var outSubCorrect = correct.substr(lenV);
            var classC = 'red';
            var keyID = value.substr(lenV-1);
            var keyIDcorrect = correct.substr(lenV-1,1);
            if(findAR(characters,keyID) != -1){
                if(value.toLowerCase() == subCorrect.toLowerCase()){
                    classC = 'green';
                    app.insideCorrectTranslate = subCorrect;
                    app.outsideCorrectTranslate = outSubCorrect;
                }
            } else {
                if(findAR(characters,keyIDcorrect) == -1){
                    classC = 'green';
                    app.insideCorrectTranslate = subCorrect;
                    app.outsideCorrectTranslate = outSubCorrect;
                    app.translatePhrase = value.substr(0,lenV-1) + keyIDcorrect;
                }
            }
            return '<span class="'+classC+'">'+value+'</span>';
        } else {
            return '';
        }
    });
    var app = new Vue({
        el: '#panel_learn',
        data: {
            phrase: '',
            translatePhrase: '',
            correctTranslate: '',
            insideCorrectTranslate: '',
            outsideCorrectTranslate: '',
            open: false
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
                        this.insideCorrectTranslate = '';
                        this.outsideCorrectTranslate = qeng.trim();
                        this.open = false;
                    }
                  }, response => {
                    console.log(response);
                  });
            },
            viewPhrase: function(){
                this.open = !this.open;
            }
        }
    });
    
    if ([].indexOf) {
        var findAR = function(array, value) {
          return array.indexOf(value);
        }
    } else {
        var findAR = function(array, value) {
          for (var i = 0; i < array.length; i++) {
            if (array[i] === value) return i;
          }
          return -1;
        }
    }
    var characters = [
        "A","B","C","D","E","F","G","H","I","J","K","L","M",
        "N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
        "a","b","c","d","e","f","g","h","i","j","k","l","m",
        "n","o","p","q","r","s","t","u","v","w","x","y","z",
        "1","2","3","4","5","6","7","8","9","0"," ",",","."];
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