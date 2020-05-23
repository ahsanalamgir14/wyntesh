<template>
  <div class="app-container">
    <el-form ref="dataForm" :rules="rules" :model="temp" label-position="top"  style="">
      <el-tabs type="border-card">
        <el-tab-pane label="Welcome Letter">          
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
              
              <el-form-item prop="description">
                <tinymce v-model="temp.description"  :imageUploadButton="true" menubar="" :toolbar="tools" id="welcomeLetter" ref="welcomeLetter" :value="temp.description" :height="450" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="handleSaveWelcomeLetter"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </el-form>
  </div>
</template>

<script>
import {
  getWelcomeLetter,
  saveWelcomeLetter,
} from "@/api/admin/welcome-letter";
import Tinymce from '@/components/Tinymce'

export default {
  name: "Settings",
  components: { Tinymce },
  data() {
    return {
      tools: ['searchreplace bold italic underline strikethrough alignleft aligncenter alignright outdent indent  blockquote', 'hr bullist numlist link image charmap preview  emoticons forecolor backcolor fullscreen'],
      temp: {
        description:'',
      },
      rules: {
        description: [
          { required: true, message: "Description is required.", trigger: "blur" }
        ]
      },
      buttonLoading: false
    };
  },
  created() {
    getWelcomeLetter().then(response => {
      if(response.data){
        this.$refs.welcomeLetter.setContent(response.data.description);
        this.temp = response.data; 
      }      
    });
  },
  methods: {
    handleSaveWelcomeLetter() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          saveWelcomeLetter(this.temp).then((response) => {
            this.temp=response.data;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
  }
};
</script>

<style scoped>

.edit-input {
  padding-right: 100px;
}

.el-form-item--medium .el-form-item__content, .el-form-item--medium .el-form-item__label {
    line-height: 0px;
}
</style>
