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
      <el-table-column label="Actions" align="center" width="200" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="primary"
            :loading="buttonLoading"
            size="mini"
            @click="handleEdit(row)"
          ><i class="fas fa-edit"></i></el-button>
          <el-button
              size="mini"
              type="danger"
              @click="deleteData(row)"
          ><i class="fas fa-trash"></i></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Title" min-width="150px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.title }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Visibility" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_visible | statusFilter">{{ row.is_visible?'Visible':'Invisible' }}</el-tag>
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

    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px"  :visible.sync="dialogDownloadVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" style="">
        <el-row>
          <el-col  :xs="24" :sm="12" :md="16" :lg="16" :xl="16" >
            <el-form-item label="Title" prop="title">
              <el-input v-model="temp.title" />
            </el-form-item>

             <el-form-item label="Download Type" prop="registration">
                <el-select v-model="temp.download_type" style="width:100%" placeholder="Download Type">
                  <el-option value='url' label="URL"></el-option>
                  <el-option value='upload' label="Upload"></el-option>
                </el-select>
              </el-form-item>

            <el-form-item v-if="temp.download_type=='url' ||(temp.url && dialogStatus=='edit') " label="URL" prop="URL">
               <el-input v-model="temp.url" />
            </el-form-item>

            <el-form-item label="Download Visible?" prop="is_visible">
              <el-select v-model="temp.is_visible" style="width:100%" placeholder="Download Visible?">
                <el-option value=1 label="Yes"></el-option>
                <el-option value=0 label="No"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="12" :md="16" :lg="8" :xl="8">
            <div class="img-upload" v-if="temp.download_type=='upload'">
              <el-form-item  prop="file">
                <label for="file" >File</label>
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
                  >
                  <!-- <img v-if="temp.file" :src="temp.image" class="avatar"> -->
                  <i class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
                <p>Click to upload file.</p>
              </el-form-item>
            </div>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogDownloadVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Confirm
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchList,
  fetchDownload,
  deleteDownload,
  createDownload,
  updateDownload
} from "@/api/downloads";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'

export default {
  name: "ComplexTable",
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
        url:undefined,
        is_visible: "1",
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
        is_visible: "1",
        download_type:'upload',
        url:undefined
      },

      dialogDownloadVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        title: [{ required: true, message: 'title is required', trigger: 'blur' }]
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
        id: undefined,
        title: undefined,
        is_visible: "1",
        download_type:'upload',
        url:undefined
      };
      this.file=undefined
      this.fileList=[];
    },
    handleCreate() {
      this.fileList=[];
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogDownloadVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){
              form.append(key, form_data[key]);
            }
          }

          form.append('file', this.file);

          createDownload(form).then((data) => {
            this.list.unshift(data.data);
            this.dialogDownloadVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          });
        }
      });
      this.buttonLoading=false;
    },
    handleEdit(row) {
      this.fileList=[];
      this.file=undefined;
      row.download_type='url';
      this.temp = Object.assign({}, row); // copy obj
      if(row.is_visible==1){
        this.temp.is_visible="1"
      }else{
        this.temp.is_visible="0"
      }

      this.dialogStatus = "update";
      this.dialogDownloadVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {
      this.buttonLoading=true;
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          var form = new FormData();
          const tempData = Object.assign({}, this.temp);

          for ( var key in tempData ) {
            if(tempData[key] !== undefined && tempData[key] !== null){
              form.append(key, tempData[key]);
            }
          }

          form.append('file', this.file);

          
          updateDownload(form).then((data) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, data.data);
                break;
              }
            }
            this.dialogDownloadVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          });
        }
      });
      this.buttonLoading=false;
    },
    deleteData(row) {
        deleteDownload(row.id).then((data) => {
            this.dialogDownloadVisible = false;
            this.$notify({
                title: "Success",
                message: data.message,
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
