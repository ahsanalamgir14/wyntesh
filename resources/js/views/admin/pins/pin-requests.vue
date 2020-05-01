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
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>
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
      <el-table-column label="Actions" align="center" width="90" class-name="small-padding">
        <template slot-scope="{row}">
          <el-tooltip content="Approve" placement="right" effect="dark">
            <el-button
                size="mini"
                type="success"
                @click=""
            ><i class="fas fa-check"></i></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="Member ID" width="150px">
        <template slot-scope="{row}">
          <span class="link-type">{{ row.member?row.member.code:'-' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member Name" width="150px">
        <template slot-scope="{row}">
          <span class="link-type">{{ row.member?row.member.name:'-' }}</span>
        </template>
      </el-table-column>  
      <el-table-column label="Payment Mode" width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.payment_mode }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Reference No." width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.reference_no }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Bank Name" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.bank_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Deposit Date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.deposit_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Remarks" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.remarks }}</span>
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

    <el-dialog :title="textMap[dialogStatus]" width="85%" top="30px"  :visible.sync="dialogAchieverVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp"  >
        <el-row>
          <el-col  :xs="24" :sm="12" :md="16" :lg="16" :xl="16" >
            <el-form-item label="Title" prop="title">
              <el-input v-model="temp.title" />
            </el-form-item>

            <el-form-item label="Subtitle" prop="subtitle">
              <el-input v-model="temp.subtitle" />
            </el-form-item>

            <el-form-item label="Description" prop="description">
              <tinymce v-model="temp.description" :imageUploadButton="false"  :height="150" />
            </el-form-item>

            <el-form-item label="Date" prop="date">
              <el-date-picker
                v-model="temp.date"
                type="date"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                placeholder="End Date">
              </el-date-picker>
            </el-form-item>

             <el-form-item label="Achiever Visibility" prop="is_visible">
              <el-switch
                v-model="temp.is_visible"
                active-text="Visible"
                inactive-text="Not visible">
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="12" :md="16" :lg="8" :xl="8">
            <div class="img-upload">
              <el-form-item  prop="image">
                <label for="Image">Image</label>
                <el-upload
                  class="avatar-uploader"
                  action="#"
                   ref="upload"
                  :show-file-list="true"
                  :auto-upload="false"
                  :on-change="handleChange"
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
        <el-button @click="dialogAchieverVisible = false">
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
  fetchAchiever,
  deleteAchiever,
  createAchiever,
  updateAchiever
} from "@/api/admin/achievers";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'

export default {
  name: "all-pings",
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
      list: [
        {
          id:1,
          member:{
            code:'1000006',
            name:'Abhishek Raj'
          },
          payment_mode:'NEFT',
          reference_no:'ABR45236954',
          bank_name:'SBI',
          deposit_date:'2020-03-25',
          remarks:'ASAP'
        },
        {
          id:1,
          member:{
            code:'1000007',
            name:'Dhaval Patel'
          },
          payment_mode:'UPI',
          reference_no:'eva45236954',
          bank_name:'ICICI',
          deposit_date:'2020-03-27',
          remarks:'Generate'
        },
      ],
      total: 2,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        title: undefined,
        subtitle: undefined,
        description:undefined,
        date: undefined,
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
        date: undefined,
        is_visible: false,
        image:undefined
      },

      dialogAchieverVisible:false,
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
    //this.getList();
  },
  methods: {
    handleChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.file=f.raw      
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
        date: undefined,
        is_visible: false,
        image:undefined
      };
      this.file=undefined
      this.fileList=[];
    },
    handleCreate() {
      this.fileList=[];
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogAchieverVisible = true;
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

          form.append('image', this.file);

          createAchiever(form).then((data) => {
            this.list.unshift(data.data);
            this.dialogAchieverVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
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
      this.temp = Object.assign({}, row); // copy obj
      if(row.is_visible==1){
        this.temp.is_visible=true
      }else{
        this.temp.is_visible=false
      }
      this.dialogStatus = "update";
      this.dialogAchieverVisible = true;
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

          form.append('image', this.file);

          
          updateAchiever(form).then((data) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, data.data);
                break;
              }
            }
            this.dialogAchieverVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
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
        deleteAchiever(row.id).then((data) => {
            this.dialogAchieverVisible = false;
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
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Title",
          "Subtitle",
          "Date",
          "Created at"
        ];
        const filterVal = [
          "id",
          "title",
          "subtitle",
          "date",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "achievers"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else {
            return v[j];
          }
        })
      );
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
