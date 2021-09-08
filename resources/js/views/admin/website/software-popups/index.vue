<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="success"
        @click="handleCreate"
      ><i class="fas fa-plus"></i> Add</el-button>
    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="primary"
            :loading="buttonLoading"
            icon="el-icon-edit"
            circle
            @click="handleEdit(row)"
          ></el-button>
          <el-button
              icon="el-icon-delete"
              circle
              type="danger"
              @click="deleteData(row)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Title" min-width="350px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.title }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Subtitle" width="170px" align="left">
        <template slot-scope="{row}">
          <span>{{ row.subtitle }}</span>
        </template>
      </el-table-column> 
       <el-table-column label="Image" min-width="150px">
        <template slot-scope="{row}">
          <a :href="row.image" class="link-type" type="primary" target="_blank">View Image</a>
        </template>
      </el-table-column> 
      <el-table-column label="From time" width="170px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.from_time }}</span>
        </template>
      </el-table-column>
      <el-table-column label="To time" width="170px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.to_time }}</span>
        </template>
      </el-table-column>
      <el-table-column label="CTA Text" min-width="150px">
        <template slot-scope="{row}">
          <span  >{{ row.cta_text }}</span>
        </template>
      </el-table-column>
      <el-table-column label="CTA Link" min-width="150px">
        <template slot-scope="{row}">
          <span  >{{ row.cta_link }}</span>
        </template>
      </el-table-column>
       <el-table-column label="Created at" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

    <el-dialog :title="textMap[dialogStatus]" width="70%" top="30px"  :visible.sync="dialogPopupVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" style="">
        <el-row>
          <el-col  :xs="24" :sm="12" :md="16" :lg="16" :xl="16" >
            <el-form-item label="Title" prop="title">
              <el-input v-model="temp.title" />
            </el-form-item>

            <el-form-item label="Subtitle" prop="subtitle">
              <el-input v-model="temp.subtitle" />
            </el-form-item>
             <el-form-item label="Description" prop="description">
                <el-input
                  type="textarea"
                  :rows="4"
                  placeholder="Description"
                  v-model="temp.description">
                </el-input>
              </el-form-item>
             <el-row :gutter="20">
             
              <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                  <el-form-item label="From Time" prop="from_time">
                    </br>
                    <el-date-picker
                      v-model="temp.from_time"
                      type="datetime"
                      width="100%"
                      format="yyyy-MM-dd hh:mm:ss"
                      value-format="yyyy-MM-dd hh:mm:ss"
                      placeholder="From Time">
                    </el-date-picker>
                  </el-form-item>

                  <el-form-item label="To Time" prop="to_time">
                    </br>
                    <el-date-picker
                      v-model="temp.to_time"
                      type="datetime"
                      format="yyyy-MM-dd hh:mm:ss"
                      value-format="yyyy-MM-dd hh:mm:ss"
                      placeholder="To Time">
                    </el-date-picker>
                  </el-form-item>

                   <el-form-item label="Popup Visibility" prop="is_visible">
                    </br>
                    <el-switch
                      v-model="temp.is_visible"
                      active-text="Visible"
                      inactive-text="Not visible">
                    </el-switch>
                  </el-form-item>
              </el-col>
               <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
               
                 <el-form-item label="CTA Text" prop="cta_text">
                  <el-input v-model="temp.cta_text" />
                </el-form-item>
                <el-form-item label="CTA Link" prop="cta_link">
                  <el-input v-model="temp.cta_link" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-col>
          <el-col  :xs="24" :sm="12" :md="16" :lg="8" :xl="8">
            <div class="img-upload">
              <el-form-item  prop="image">
                <label for="Image" >Image</label>
                <el-upload
                  class="avatar-uploader"
                  action="#"
                   ref="upload"
                  :show-file-list="true"
                  :auto-upload="false"
                  :on-change="handleChange"
                  :on-remove="handleRemove"
                  :limit="3"
                  :file-list="fileList"
                  :on-exceed="handleExceed"
                  accept="image/png, image/jpeg">
                  <img v-if="temp.image" :src="temp.image" class="avatar">
                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
                <p>Click to upload image.</p>
              </el-form-item>
            </div>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPopupVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchList,
  fetchSoftwarePopup,
  deleteSoftwarePopup,
  createSoftwarePopup,
  updateSoftwarePopup
} from "@/api/admin/software-popups";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import Tinymce from '@/components/Tinymce'

export default {
  name: "software-popups",
  components: { Pagination,Tinymce },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        title: undefined,
        subtitle: undefined,
        description:undefined,
        from_time: undefined,
        to_time:undefined,
        image:undefined,
        is_visible: 0,
        sort: "+id"
      },
      fileList:[],
      file:undefined,
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        title: undefined,
        subtitle: undefined,
        description:undefined,
        from_time: undefined,
        to_time:undefined,
        is_visible: false,
        image:undefined,
        cta_link:undefined,
        cta_text:undefined
      },

      dialogPopupVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        title: [{ required: true, message: 'title is required', trigger: 'blur' }],
        date: [{  required: true, message: 'Date is required', trigger: 'blur' }]
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    handleChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.file=f.raw      
    },
    handleRemove(file, fileList) {
       this.file=undefined;
       this.fileList=[];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    getList() {
      this.listLoading = true;
      fetchList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    resetTemp() {
      this.temp = {
        title: undefined,
        subtitle: undefined,
        description:undefined,
        from_time: undefined,
        to_time:undefined,
        is_visible: false,
        image:undefined,
        cta_link:undefined,
        cta_text:undefined
      };
      this.file=undefined
      this.fileList=[];
    },
    handleCreate() {
      this.fileList=[];
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogPopupVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {      
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);

          createSoftwarePopup(form).then((data) => {
            this.list.unshift(data.data);
            this.dialogPopupVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleEdit(row) {
      this.fileList=[];
      this.file=undefined;
      this.temp = Object.assign({}, row); // copy obj
      if(row.is_visible==1){
        this.temp.is_visible=true
      }else{
        this.temp.is_visible=false
      }
      this.dialogStatus = "update";
      this.dialogPopupVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {      
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          var form = new FormData();
          const tempData = Object.assign({}, this.temp);

          for ( var key in tempData ) {
            if(tempData[key] !== undefined && tempData[key] !== null){
              form.append(key, tempData[key]);
            }
          }

          form.append('image', this.file);

          
          updateSoftwarePopup(form).then((data) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, data.data);
                break;
              }
            }
            this.dialogPopupVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    deleteData(row) {
        deleteSoftwarePopup(row.id).then((data) => {
            this.dialogPopupVisible = false;
            this.$notify({
                title: "Success",
                message: "Delete Successfully",
                type: "success",
                duration: 2000
            });
            const index = this.list.indexOf(row);
            this.list.splice(index, 1);
        });
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}
@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}
</style>
