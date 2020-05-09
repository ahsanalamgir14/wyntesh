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
              circle
              type="primary"
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
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Parent" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.parent?row.parent.name:'' }}</span>
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

    <el-dialog :title="dialogTitle" width="40%" top="30px"  :visible.sync="dialogCategoryVisible">
      <el-form ref="categoryForm" :rules="rules" :model="temp" style="">
        <el-row>
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
            <el-form-item label="Parent Category" prop="parent_id">
              <br>
              <el-select v-model="temp.parent_id" @change="handleFilter"  clearable  style="width:100%;" filterable placeholder="Select Parent">
                <el-option
                  v-for="item in parents"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogCategoryVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" :loading="buttonLoading" @click="is_updating?updateData():createData()">
          Confirm
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchCagegories,
  deleteCategory,
  createCategory,
  updateCategory,
  getAllCategories
} from "@/api/admin/products-and-categories";
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
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        parent_id: undefined,
      },
      parents:[],
      dialogCategoryVisible:false,
      is_updating: false,
      dialogTitle:'Create',
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }]
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getAllCats();
  },
  methods: {
    
    getList() {
      this.listLoading = true;
      fetchCagegories(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getAllCats() {
      this.listLoading = true;
      getAllCategories().then(response => {
        this.parents = response.data;
      });
    },
    
    resetTemp() {
      this.temp = {
        id: undefined,
        name: undefined,
        parent_id: undefined,
      };
    },
    handleCreate() {
      this.is_updating = false;
      this.dialogTitle="Create Category"      
      this.dialogCategoryVisible = true;
      this.resetTemp();
      this.$nextTick(() => {
        this.$refs["categoryForm"].clearValidate();
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["categoryForm"].validate(valid => {
        if (valid) {         
          createCategory(this.temp).then((data) => {            
            this.dialogCategoryVisible = false;
            this.buttonLoading=false;
            this.resetTemp();
            this.getList();
            this.getAllCats();

            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
          }).catch((err)=>{
            this.buttonLoading=false;      
          });
        }
      });      
    },
    handleEdit(row) {
      this.temp = Object.assign({}, row);      
      this.is_updating = true;
      this.dialogTitle="Update Category"
      this.dialogCategoryVisible = true;
      this.$nextTick(() => {
        this.$refs["categoryForm"].clearValidate();
      });
    },
    updateData() {
      this.buttonLoading=true;
      this.$refs["categoryForm"].validate(valid => {
        if (valid) {
          const tempData = Object.assign({}, this.temp);

          updateCategory(tempData).then((data) => {
            this.dialogCategoryVisible = false;
            this.buttonLoading=false;
            this.resetTemp();
            this.getList();
            this.getAllCats();

            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
          }).catch((err)=>{
            this.buttonLoading=false;      
          });
        }
      });
    },
    deleteData(row) {
        deleteCategory(row.id).then((data) => {
            this.dialogCategoryVisible = false;
            this.getList();
            this.$notify({
                title: "Success",
                message: data.message,
                type: "success",
                duration: 2000
            });
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
