<template>
    <div>
        <div id="header">
            <div>Dropzone Images</div>
            <div><a v-bind:href="'/update'">Update Post</a></div>
        </div>

        <div class="dz" >
            <input v-model="title" type="text" class="form-control" placeholder="Title Name">
            <div ref="dropzone">
                Uploads or Drop Files
            </div>
        </div>

        <div class="mb-3 editor">
            <vue-editor useCustomImageHandler @image-added="handleImageAdded" v-model="content" />
        </div>

        <input :disabled="!isDisabled" @click.prevent="store" type="submit" class="btn btn-primary" value="Add Images">

        <div class="mt-5 images" v-if="post">
            <hr>
            <h4>{{ post.title }}</h4>
            <div v-for="image in post.images">
                <img :src="image.preview_url" class="mb-3" alt=""/>
                <img :src="image.url" class="mb-3" alt=""/>
                <hr>
            </div>
            <div class="ql-editor" v-html="post.content"></div>
        </div>
    </div>
</template>

<script>
import Dropzone from 'dropzone'
import { VueEditor } from "vue2-editor";

export default {
    name: "index",

    data(){
        return{
            dropzone: null,
            title: null,
            post: null,
            content: null
        }
    },

    mounted() {
        this.dropzone = new Dropzone(this.$refs.dropzone, {
            url: '/api/image/store',
            autoProcessQueue: false,
            addRemoveLinks: true
        })

        this.getPosts()

    },

    updated() {
       // this.isDisabled()
    },

    methods:{
        store(){
            const data = new FormData()
            const files = this.dropzone.getAcceptedFiles()
            files.forEach(file => {
                data.append('images[]', file)
                this.dropzone.removeFile(file)
            })
            data.append('title', this.title)
            data.append('content', this.content)
            this.title = ''
            this.content = ''
            axios.post('/api/image/store', data)
                .then(res => {
                    this.getPosts()
                })
        },

        getPosts(){
            axios.get('/api/image/get')
                .then(res =>{
                    //console.log(res.data.data);
                    this.post = res.data.data
                })
        },

        handleImageAdded(file, Editor, cursorLocation, resetUploader) {
            const formData = new FormData();
            formData.append("image", file);

            axios.post('/api/image/editor', formData)
                .then(result => {
                    const url = result.data.url; // Get url from response
                    Editor.insertEmbed(cursorLocation, "image", url);
                    resetUploader();
                })
                .catch(err => {
                    console.log(err);
                });
        },

      isDisabled(){
            console.log(document.getElementsByClassName('dz-preview'));
        }

    },

    components:{
        VueEditor
    }


}
</script>

<style >
    div#header{width: 33%; padding-left: 50px; margin-bottom: 20px; padding-top: 20px; display: flex; justify-content: space-between; text-align: center; align-items: center;}
    div#header div:first-child{font-size: 30px;}
    div.dz input, div.editor{width: 30%; margin-left: 50px;}
    input[type=submit]{margin-left: 50px;}
    div.dz > div{padding: 80px 30px; width: 30%; margin: 20px 0 20px 50px; cursor: pointer; background-color: #2d3748; border: 1px solid #fff;
        color: #fff; border-radius: 5px; text-align: center;}
    div.dz > div:hover{background-color: #0c63e4;}

    div.images{margin-left: 50px; width: 30%;}
    div.images h4{text-align: center;}
    div.images img{display: block; margin: 5px; max-width: 100%;}

    div.dz-success-mark, div.dz-error-mark{display: none;}
</style>
