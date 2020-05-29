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
        v-if="checkRole(['superadmin'])"
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
      <el-table-column label="Actions" align="center" width="200" class-name="small-padding" v-if="checkRole(['superadmin'])">
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
          <span >{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Exection Type" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.exection_type }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Exection Day" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.exection_day }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Exection Time" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.exection_time }}</span>
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

    <el-dialog :title="dialogTitle" width="60%" top="30px"  :visible.sync="dialogPayoutTypeVisible">
      <el-form ref="transactionTypeForm" :rules="rules" :model="temp" style="">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
            <el-form-item label="Exectition Type" prop="exection_type">
              <el-input v-model="temp.exection_type" />
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Exectition Day" prop="exection_day">
              <el-input type="number" v-model="temp.exection_day" />
            </el-form-item>
            <el-form-item label="Exectition Time" prop="exection_time">
              <br>
              <el-time-select
                style="width: 100%"
                v-model="temp.exection_time"
                :picker-options="pickerOptions"
                placeholder="Select time">
              </el-time-select>
            </el-form-item>
          </el-col>

        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPayoutTypeVisible = false">
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
  fetchTransactionType,
  deletePayoutType,
  createPayoutType,
  updatePayoutType
} from "@/api/admin/payout-types";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import role from '@/directive/role'; 
import checkRole from '@/utils/role';

export default {
  name: "TransactionTypes",
  components: { Pagination },
  directives: { waves,role },
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
        sort: "-id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        exection_type: undefined,
        exection_day: undefined,
        exection_time: undefined,
      },
      pickerOptions:{
        start: '00:00',
        step: '00:30',
        end: '24:00'
      },
      dialogPayoutTypeVisible:false,
      dialogStatus: "",
      dialogTitle:"",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        exection_type: [{ required: true, message: 'Exectition type is required', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    checkRole,
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
        exection_type: undefined,
        exection_day: undefined,
        exection_time: undefined,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle="Create Payout Type";
      this.dialogPayoutTypeVisible = true;
      this.$nextTick(() => {
        this.$refs["transactionTypeForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["transactionTypeForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          createPayoutType(this.temp).then((data) => {
            this.getList();
            this.dialogPayoutTypeVisible = false;
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
    handleEdit(row) {
     
      this.temp = Object.assign({}, row); // copy obj
      this.dialogStatus = "update";
      this.dialogTitle="Update Payout Type";
      this.dialogPayoutTypeVisible = true;
      this.$nextTick(() => {
        this.$refs["transactionTypeForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["transactionTypeForm"].validate(valid => {
        if (valid) {          
          this.buttonLoading=true;
          const tempData = Object.assign({}, this.temp);
          updatePayoutType(tempData).then((data) => {
            this.getList();
            this.dialogPayoutTypeVisible = false;
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
        deletePayoutType(row.id).then((data) => {
            this.dialogPayoutTypeVisible = false;
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
