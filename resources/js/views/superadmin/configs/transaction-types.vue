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
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.name }}</span>
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

    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px"  :visible.sync="dialogTransactionTypeVisible">
      <el-form ref="transactionTypeForm" :rules="rules" :model="temp" style="">
        <el-row>
          <el-col  :xs="24" :sm="12" :md="16" :lg="16" :xl="16" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>


            <el-form-item label="Is Active ?" prop="is_active">
              <el-select v-model="temp.is_active" style="width:100%" placeholder="Is Active ?">
                <el-option value=1 label="Yes"></el-option>
                <el-option value=0 label="No"></el-option>
              </el-select>
            </el-form-item>
          </el-col>

        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogTransactionTypeVisible = false">
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
  fetchTransactionType,
  deleteTransactionType,
  createTransactionType,
  updateTransactionType
} from "@/api/superadmin/transaction-types";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";

export default {
  name: "TransactionTypes",
  components: { Pagination },
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
        search: undefined,
        is_active: "1",
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        is_active: "1",
      },

      dialogTransactionTypeVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }]
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
        name: undefined,
        is_active: "1",
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTransactionTypeVisible = true;
      this.$nextTick(() => {
        this.$refs["transactionTypeForm"].clearValidate();
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["transactionTypeForm"].validate(valid => {
        if (valid) {
         
          createTransactionType(this.temp).then((data) => {
            this.list.unshift(data.data);
            this.dialogTransactionTypeVisible = false;
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
     
      this.temp = Object.assign({}, row); // copy obj
      if(row.is_active==1){
        this.temp.is_active="1"
      }else{
        this.temp.is_active="0"
      }

      this.dialogStatus = "update";
      this.dialogTransactionTypeVisible = true;
      this.$nextTick(() => {
        this.$refs["transactionTypeForm"].clearValidate();
      });
    },
    updateData() {
      this.buttonLoading=true;
      this.$refs["transactionTypeForm"].validate(valid => {
        if (valid) {
         
          const tempData = Object.assign({}, this.temp);

          
          updateTransactionType(tempData).then((data) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, data.data);
                break;
              }
            }
            this.dialogTransactionTypeVisible = false;
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
        deleteTransactionType(row.id).then((data) => {
            this.dialogTransactionTypeVisible = false;
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
