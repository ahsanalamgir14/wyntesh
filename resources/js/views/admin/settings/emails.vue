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
        icon="el-icon-plus"
        type="success"
        @click="handleCreate"
      >Add New Email</el-button>
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
            circle
            icon="el-icon-edit"
            @click="handleEdit(row)"
          ></el-button>
          <el-button
              circle
              type="danger"
              icon="el-icon-delete"
              @click="deleteData(row)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Title" min-width="150px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.title }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Status" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_active | statusFilter">{{ row.is_active=='1' ?'Active':'Deactive' }}</el-tag>
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

    <el-dialog :title="dialogTitle" width="90%" top="30px"  :visible.sync="dialogEmailsVisible">
      <el-form ref="emailForm" :rules="rules" :model="temp" style="">
        <el-row :gutter="10">
          <el-col  :xs="24" :sm="24" :md="5" :lg="5" :xl="5" >
            <el-form-item label="Title" prop="title">
              <el-input v-model="temp.title" />
            </el-form-item>
            <el-form-item label="Is Active ?" prop="is_active">
              <el-select v-model="temp.is_active" style="width:100%" placeholder="Is Active ?">
                <el-option :value="1" label="Yes"></el-option>
                <el-option :value="0" label="No"></el-option>
              </el-select>
            </el-form-item>
            
          </el-col>
          <el-col  :xs="24" :sm="24" :md="19" :lg="19" :xl="19" >
            <el-form-item label="Email Contant" prop="description">
              <br>
              <tinymce v-model="temp.description"  :imageUploadButton="true" :toolbar="tools" id="emailContent" ref="emailContent" :value="temp.description" :height="450" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogEmailsVisible = false">
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
  getEmail,
  deleteEmail,
  createEmail,
  updateEmail
} from "@/api/admin/emails";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import Tinymce from '@/components/Tinymce'

export default {
  name: "PaymentModes",
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
      tools: ['searchreplace bold italic underline strikethrough alignleft aligncenter alignright outdent indent  blockquote', 'hr bullist numlist link image charmap preview  emoticons forecolor backcolor fullscreen'],
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        is_active: 1,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        title: undefined,
        is_active: 1,
        description:undefined,
      },

      dialogEmailsVisible:false,
      dialogStatus: "",
      dialogTitle: "",
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
        description:undefined,
        is_active: 1,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogTitle = "Add Email";
      this.dialogStatus = "create";
      this.dialogEmailsVisible = true;
      this.$nextTick(() => {
        this.$refs["emailForm"].clearValidate();
        this.$refs.emailContent.setContent("");
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["emailForm"].validate(valid => {
        if (valid) {
         
          createEmail(this.temp).then((data) => {
            this.getList();
            this.dialogEmailsVisible = false;
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
      getEmail(row.id).then(response => {
        if(response.data){
          this.$refs.emailContent.setContent(response.data.description);
          this.temp = response.data; 
        }      
      });

      if(this.temp.is_active==1){
        this.temp.is_active=1
      }else{
        this.temp.is_active=0
      }
      this.dialogTitle = "Update Email";
      this.dialogStatus = "update";
      this.dialogEmailsVisible = true;
      this.$nextTick(() => {
        this.$refs["emailForm"].clearValidate();
      });
    },
    updateData() {      
      this.$refs["emailForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;         
          const tempData = Object.assign({}, this.temp);
          updateEmail(tempData).then((data) => {
           this.getList();
            this.dialogEmailsVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
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
        deleteEmail(row.id).then((data) => {
            this.dialogEmailsVisible = false;
            this.$notify({
                title: "Success",
                message: data.message,
                type: "success",
                duration: 2000
            });
            this.getList();
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
