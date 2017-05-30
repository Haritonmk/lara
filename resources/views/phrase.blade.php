@extends('layouts.app')

@section('content')
<script src="../js/vue.min.js"></script>
<script src="../js/vue-resource.js"></script>
<div id="panel_learn" class="container">
    <div class="col-sm-6">
        <div id="phrase">@{{phrase}}</div>
        <textarea v-model="translatePhrase"></textarea>
        <button @click="newPhrase()" class="btn btn-primary">New Phrase</button>
    </div>
    <div class="col-sm-6">
        <div v-html="compiled">@{{translatePhrase | correct:correctTranslate}}</div>
    </div>
</div>
<script>
    Vue.filter('correct',function(value,correct){
        
        return value;
    });
    var app = new Vue({
        el: '#panel_learn',
        data: {
            phrase: '',
            translatePhrase: '',
            correctTranslate: ''
        },
        methods: {
            newPhrase: function(){
                this.translatePhrase = '';
                this.$http.get('{{ url("/words/phrase") }}').then(response => {
                    if (response.ok) {
                        this.phrase = response.body.qrus;
                        this.correctTranslate = response.body.qeng;
                    }
                  }, response => {
                    console.log(response);
                  });
            }
        }
    });
</script>
@endsection