<template>
  <div class="app-container">
    <el-form ref="dataForm" :rules="rules" :model="temp" label-position="top"  style="">
      <el-tabs type="border-card">
        <el-tab-pane label="Member Notice">          
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
              <el-form-item label="Title" prop="title">
                <el-input v-model="temp.title" />
              </el-form-item>
              <el-form-item label="Description" prop="description">
                <el-input
                  type="textarea"
                  :rows="3"
                  placeholder="Description"
                  v-model="temp.description">
                </el-input>
              </el-form-item>
              <el-form-item label="Notice Visibility" prop="is_active">
                <el-switch
                  v-model="temp.is_active"
                  active-text="Visible"
                  inactive-text="Not visible">
                </el-switch>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="handleSaveNotice"
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
  getNotice,
  saveNotice,
} from "@/api/admin/notices";

export default {
  name: "Settings",
  data() {
    return {
      temp: {
        title:undefined,
        description:undefined,
        is_active:true,
      },
      tds_percentage:undefined,
      rules: {
        title: [
          { required: true, message: "Title is required.", trigger: "blur" }
        ],
        description: [
          { required: true, message: "Description is required.", trigger: "blur" }
        ]
      },
      buttonLoading: false
    };
  },
  created() {
    getNotice().then(response => {
      if(response.data){
        this.temp = response.data;
        this.temp.is_active=this.temp.is_active==1?true:false;  
      }      
    });
  },
  methods: {
    handleSaveNotice() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          saveNotice(this.temp).then((response) => {
            this.temp=response.data;
            this.temp.is_active=this.temp.is_active==1?true:false;
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
