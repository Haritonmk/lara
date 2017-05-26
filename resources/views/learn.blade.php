@extends('layouts.app')

@section('content')
<script src="js/vue.min.js"></script>
<!-- v-html="this.$options.filters.unescape(words[currentCategory][currentWord]['auth']+'<br/>'+words[currentCategory][currentWord]['qeng'])" -->
<div id="panel_learn">
    <modal v-if="showModal" @close="showModal = false">
        <h3 slot="header">@{{words[currentCategory][currentWord]['eng']}}</h3>
        <div slot="body" v-html="this.$options.filters.unescape(words[currentCategory][currentWord]['qeng'])" >@{{ words[currentCategory][currentWord]['auth'] | unescape }} <br /> @{{words[currentCategory][currentWord]['qeng'] | unescape}}</div>
    </modal>
    <div class="col-sm-4">
        <ol>
            <li v-for="category in categories">
                <a href="#" v-on:click.stop="getData(category.id)">@{{category.namerus}}</a>
            </li>
        </ol>
    </div>
    <div class="col-sm-8">
        <table class="table table-bordered table-striped">
            <tr v-for="(word, index) in words[currentCategory]" @click="currentWord = index; showModal = true">
                <td>@{{word.eng}}</td>
                <td>@{{word.rus}}</td>
            </tr>
        </table>
    </div>
</div>
<!-- template for the modal component -->
<script type="text/x-template" id="modal-template">
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container">

          <div class="modal-header">
            <slot name="header">
              default header
            </slot>
          </div>

          <div class="modal-body">
            <slot name="body">
              default body
            </slot>
          </div>

          <div class="modal-footer">
            <slot name="footer">
              <button class="modal-default-button" @click="$emit('close')">
                OK
              </button>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </transition>
</script>
<script>
    Vue.component('modal', {
        template: '#modal-template'
    });
    
    Vue.filter('unescape', function (value) {
        value = value.replace(/&rsquo;/g,"'");
        value = value.replace(/&nbsp;/g," ");
        value = value.replace(/&quot;/g,'"');
        return value;
    });
    
    var app = new Vue({
        el: '#panel_learn',
        data: {
            categories: [],
            words: [],
            currentCategory: 0,
            currentWord: 0,
            showModal: false
        },
        created: function(){
            /*this.$http.get('{{ url("/categories") }}').then((response) => {
                console.log(response);
            }, (response) => {
                console.log('error',response);
            });*/
            fetch('{{ url("/categories") }}')
            .then((response) => {
                if(response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok');
            })
            .then((json) => {
                this.categories = json;
            })
            .catch((error) => {
                console.log(error);
            });
        },
        methods: {
            getData: function(id){
                if(typeof(this.words[id]) == "undefined"){
                    fetch('{{ url("/categories") }}/'+id)
                    .then((response) => {
                        if(response.ok) {
                            return response.json();
                        }
                        throw new Error('Network response was not ok');
                    })
                    .then((json) => {
                        this.words[id] = json;
                        this.currentCategory = id;
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                } else {
                    this.currentCategory = id;
                }
            }
        }
    });
</script>
<style>
    .modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, .5);
  display: table;
  transition: opacity .3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 300px;
  margin: 0px auto;
  padding: 20px 30px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
  transition: all .3s ease;
  font-family: Helvetica, Arial, sans-serif;
}

.modal-header h3 {
  margin-top: 0;
  color: #42b983;
}

.modal-body {
  margin: 20px 0;
}

.modal-default-button {
  float: right;
}

.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}
</style>
@endsection