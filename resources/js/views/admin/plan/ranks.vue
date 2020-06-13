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
          <span >{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Capping" min-width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.capping }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Personal BV Condition" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.personal_bv_condition }}</span>
        </template>
      </el-table-column>
      <el-table-column label="BV From" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.bv_from }}</span>
        </template>
      </el-table-column>
      <el-table-column label="BV To" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.bv_to }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Leg Rank" min-width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.leg_rank?row.leg_rank.name:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Leg Rank Count" min-width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.leg_rank_count }}</span>
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

    <el-dialog :title="dialogTitle" width="60%" top="30px"  :visible.sync="dialogRanksVisible">
      <el-form ref="rankForm" :rules="rules" :model="temp" style="">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
            <el-form-item label="Capping" prop="capping">
              <el-input type="number" v-model="temp.capping" />
            </el-form-item>
            <el-form-item label="BV From" prop="bv_from">
              <el-input type="number" v-model="temp.bv_from" />
            </el-form-item>
            <el-form-item label="BV To" prop="bv_to">
              <el-input type="number" v-model="temp.bv_to" />
            </el-form-item>
            
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Leg Rank" prop="leg_rank">
              <br>
              <el-select v-model="temp.leg_rank"  clearable  style="width:100%;"  placeholder="Select Leg Rank">
                <el-option
                  v-for="item in legRanks"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Leg rank count" prop="leg_rank_count">
              <el-input type="number" v-model="temp.leg_rank_count" />
            </el-form-item>
            <el-form-item label="Personal BV Condition" prop="personal_bv_condition">
              <el-input type="number" v-model="temp.personal_bv_condition" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogRanksVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="is_updating?updateData():createData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchList,
  deleteRank,
  createRank,
  updateRank,
  getAllRanks
} from "@/api/admin/ranks";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
 import role from '@/directive/role'; 
import checkRole from '@/utils/role';

export default {
  name: "ComplexTable",
  components: { Pagination,Pagination },
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
        capping: undefined,
        bv_from: undefined,
        bv_to: undefined,
        leg_rank: undefined,
        leg_rank_count: undefined,
        personal_bv_condition:undefined,
      },
      legRanks:[],
      dialogRanksVisible:false,
      is_updating: false,
      dialogTitle:'Create',
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        capping: [{ required: true, message: 'Capping is required', trigger: 'blur' }],
        personal_bv_condition: [{ required: true, message: 'Personal BV Condition is required', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getAllRanks();
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
    getAllRanks() {
      this.listLoading = true;
      getAllRanks().then(response => {
        this.legRanks = response.data;
      });
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        name: undefined,
        capping: undefined,
        bv_from: undefined,
        bv_to: undefined,
        leg_rank: undefined,
        leg_rank_count: undefined,
        personal_bv_condition:undefined,
      };
    },
    handleCreate() {
      this.is_updating = false;
      this.dialogTitle="Create Rank"      
      this.dialogRanksVisible = true;
      this.resetTemp();
      this.$nextTick(() => {
        this.$refs["rankForm"].clearValidate();
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["rankForm"].validate(valid => {
        if (valid) {   
          this.buttonLoading=true;
          createRank(this.temp).then((data) => {            
            this.dialogRanksVisible = false;
            this.buttonLoading=false;
            this.resetTemp();
            this.getList();
            this.getAllRanks();

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
      this.dialogTitle="Update Rank"
      this.dialogRanksVisible = true;
      this.$nextTick(() => {
        this.$refs["rankForm"].clearValidate();
      });
    },
    updateData() {    
      this.$refs["rankForm"].validate(valid => {
        if (valid) {
        this.buttonLoading=true;          
          updateRank(this.temp).then((data) => {
            this.dialogRanksVisible = false;
            this.buttonLoading=false;
            this.resetTemp();
            this.getList();
            this.getAllRanks();

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
        deleteRank(row.id).then((data) => {
            this.dialogRanksVisible = false;
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
