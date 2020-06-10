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
      <el-table-column type="expand">
        <template slot-scope="{row}">
          <div v-for="parameter in row.income_parameters">
            <p><b>Name:</b> {{ parameter.name }}</p>
            <p><b>Value 1:</b> {{ parameter.value_1 }}</p>
            <p v-if="parameter.value_2" ><b>Value 2:</b> {{ parameter.value_2 }}</p>
            <p v-if="parameter.value_3"><b>Value 3:</b> {{ parameter.value_3 }}</p>
            <p v-if="parameter.value_4"><b>Value 4:</b> {{ parameter.value_4 }}</p>
            <p v-if="parameter.value_5"><b>Value 5:</b> {{ parameter.value_5 }}</p>
          </div>
          <div v-for="type in row.payout_type">
            <p><b>Payout Type:</b> {{ type.name }}</p>
          </div>
        </template>
      </el-table-column>
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
            v-if="checkRole(['superadmin'])"
            type="primary"
            :loading="buttonLoading"
            circle
            icon="el-icon-edit"
            @click="handleEdit(row)"
          ></el-button>          
            <el-button
              type="warning"
              :loading="buttonLoading"
              circle
              icon="el-icon-s-unfold"
              @click="showParameters(row)"
            ></el-button>
          <el-button
              v-if="checkRole(['superadmin'])"
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

    <el-dialog :title="dialogTitle" width="60%" top="30px"  :visible.sync="dialogIncomeVisible">
      <el-form ref="incomeForm" :rules="rules" :model="temp" style="">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
            <el-form-item label="Code" prop="code">
              <el-input v-model="temp.code" />
            </el-form-item>
            <el-form-item label="Is Active ?" prop="is_active">
              <el-select v-model="temp.is_active" style="width:100%" placeholder="Is Active ?">
                <el-option value=1 label="Yes"></el-option>
                <el-option value=0 label="No"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Capping" prop="capping">
              <el-input type="number" v-model="temp.capping" />
            </el-form-item>
            <el-form-item label="Select Payout Type" prop="payout_types">
                  <el-select v-model="temp.payout_types" filterable multiple placeholder="Select Payout Type">
                    <el-option
                      v-for="item in payoutTypeList"
                      :key="item.id"
                      :label="item.name"
                      :value="item.id">
                    </el-option>
                  </el-select>
                </el-form-item>
            <el-form-item label="Description" prop="description">
              <el-input type="textarea" v-model="temp.description" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogIncomeVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>

    <el-dialog :title="dialogTitle" width="80%" top="30px"  :visible.sync="dialogIncomeParametersVisible">
      <div class="filter-container">
        <el-input
          v-model="parameterListQuery.search"
          placeholder="Search Records"
          style="width: 200px;"
          class="filter-item"
          @keyup.enter.native="handleParameterFilter"
        />
        <el-button
          v-waves
          class="filter-item"
          type="primary"
          icon="el-icon-search"
          @click="handleParameterFilter"
        >Search</el-button>
        <el-button
          v-if="checkRole(['superadmin'])"
          class="filter-item"
          style="margin-left: 10px;"
          type="success"
          @click="handleCreateParameter"
        ><i class="fas fa-plus"></i> Add</el-button>
      </div>

      <el-table
        :key="tableKey"
        v-loading="listLoading"
        :data="parameterList"
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
              @click="handleParameterEdit(row)"
            ></el-button>
            <el-button
                v-if="checkRole(['superadmin'])"
                circle
                type="danger"
                icon="el-icon-delete"
                @click="deleteParameter(row)"
            ></el-button>
          </template>
        </el-table-column>
        <el-table-column label="Name" min-width="150px">
          <template slot-scope="{row}">
            <span  >{{ row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Value 1" min-width="150px">
          <template slot-scope="{row}">
            <span >{{ row.value_1 }}</span>
          </template>
        </el-table-column>

         <el-table-column label="Created at" width="150px" align="center">
          <template slot-scope="{row}">
            <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
          </template>
        </el-table-column>
      </el-table>

      <pagination
        v-show="parameterTotal>0"
        :total="parameterTotal"
        :page.sync="parameterListQuery.page"
        :limit.sync="parameterListQuery.limit"
        @pagination="getParameterList"
      />
      </el-dialog>

       <el-dialog :title="dialogIncomeParameterTitle" width="60%" top="30px"  :visible.sync="dialogParameterSaveVisible">
      <el-form ref="incomeParameterForm" :rules="parameterRules" :model="parameterTemp" style="">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Name" prop="name">
              <el-input v-model="parameterTemp.name" />
            </el-form-item>
            <el-form-item label="Value 1" prop="value_1">
              <el-input v-model="parameterTemp.value_1" />
            </el-form-item>
            <el-form-item label="Value 2" prop="value_2">
              <el-input v-model="parameterTemp.value_2" />
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Value 3" prop="value_3">
              <el-input v-model="parameterTemp.value_3" />
            </el-form-item>
            <el-form-item label="Value 4" prop="value_4">
              <el-input v-model="parameterTemp.value_4" />
            </el-form-item>
            <el-form-item label="Value 5" prop="value_5">
              <el-input v-model="parameterTemp.value_5" />
            </el-form-item>            
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogParameterSaveVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createParameter():updateParameter()">
          Save
        </el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import {
  fetchList,
  fetchIncome,
  createIncome,
  updateIncome,
  fetchListIncomeParameter,
  deleteIncomeParameter,
  createIncomeParameter,
  updateIncomeParameter
} from "@/api/admin/incomes";
import {getAllPayoutTypes} from "@/api/admin/payout-types";

import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import role from '@/directive/role'; 
import checkRole from '@/utils/role';

export default {
  name: "Incomes",
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
      parameterList: null,
      parameterTotal: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        is_active: "1",
        sort: "-id"
      },
      parameterListQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        income_id:0,
        is_active: "1",
        sort: "-id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        description: undefined,
        code: undefined,
        capping: undefined,
        payout_types:[],
        is_active: "1",
      },
      parameterTemp: {
        income_id: undefined,
        name: undefined,
        value_1: undefined,
        value_2: undefined,
        value_3: undefined,
        value_4: undefined,
        value_5: undefined,
      },
      payoutTypeList:[],
      dialogIncomeVisible:false,
      dialogIncomeParametersVisible:false,
      dialogParameterSaveVisible:false,
      dialogIncomeParameterTitle:"",
      dialogStatus: "",
      dialogTitle: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        code: [{ required: true, message: 'Income code is required', trigger: 'blur' }],
        payout_types: [{ required: true, message: 'Payout types are required', trigger: 'blur' }],
      },
      parameterRules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        value_1: [{ required: true, message: 'Value 1 is required', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    getAllPayoutTypes().then(response => {
      this.payoutTypeList = response.data
    });
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
    getParameterList() {
      this.listLoading = true;
      fetchListIncomeParameter(this.parameterListQuery).then(response => {
        this.parameterList = response.data.data;
        this.parameterTotal = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },    
    resetTemp() {
      this.temp = {
        id: undefined,
        name: undefined,
        description: undefined,
        payout_types:[],
        code: undefined,
        capping: undefined,
        is_active: "1",
      };
    },
    resetParameterTemp() {
      this.parameterTemp = {
        income_id: undefined,
        name: undefined,
        value_1: undefined,
        value_2: undefined,
        value_3: undefined,
        value_4: undefined,
        value_5: undefined,
      };
    },
    showParameters(row){
      this.resetParameterTemp();
      this.parameterTemp.income_id=row.id;
      this.dialogIncomeParametersVisible=true;
      this.parameterListQuery.income_id=row.id
      this.getParameterList();
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle="Create Parameter";
      this.dialogIncomeVisible = true;
      this.$nextTick(() => {
        this.$refs["incomeForm"].clearValidate();
      });
    },
    handleCreateParameter(row) {
      this.dialogStatus = "create";
      this.dialogIncomeParameterTitle="Create Parameter";
      this.dialogParameterSaveVisible = true;
      this.$nextTick(() => {
        this.$refs["incomeParameterForm"].clearValidate();
      });
    },
    createData() {      
      this.$refs["incomeForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          createIncome(this.temp).then((data) => {
            this.getList();
            this.dialogIncomeVisible = false;
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
    createParameter() {      
      this.$refs["incomeParameterForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          createIncomeParameter(this.parameterTemp).then((data) => {
            this.getParameterList();
            this.dialogParameterSaveVisible = false;
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
      row.payout_types=[];
      this.temp = Object.assign({}, row); // copy obj
      if(row.is_active==1){
        this.temp.is_active="1"
      }else{
        this.temp.is_active="0"
      }

      var keys = [];

      row.payout_type.map(pay => {
          keys.push(pay.id);
      })

      keys = keys.filter((item, i, ar) => ar.indexOf(item) === i);
      this.temp.payout_types=keys;

      this.dialogStatus = "update";
      this.dialogTitle="Update Income";
      this.dialogIncomeVisible = true;
      this.$nextTick(() => {
        this.$refs["incomeForm"].clearValidate();
      });
    },
    handleParameterEdit(row) {
     
      this.parameterTemp = Object.assign({}, row); // copy obj
    
      this.dialogStatus = "update";
      this.dialogIncomeParameterTitle="Update Parameter";
      this.dialogParameterSaveVisible = true;
      this.$nextTick(() => {
        this.$refs["incomeParameterForm"].clearValidate();
      });
    },
    updateData() {
      
      this.$refs["incomeForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          const tempData = Object.assign({}, this.temp);
          updateIncome(tempData).then((data) => {
            this.getList();
            this.dialogIncomeVisible = false;
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
    updateParameter() {
      
      this.$refs["incomeParameterForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          const tempData = Object.assign({}, this.parameterTemp);
          updateIncomeParameter(tempData).then((data) => {
            this.getParameterList();
            this.dialogParameterSaveVisible = false;
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
      deleteIncome(row.id).then((data) => {
        this.dialogIncomeVisible = false;
        this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
        });
        this.getList();
      });
    },
    deleteParameter(row) {
      deleteIncomeParameter(row.id).then((data) => {
        this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
        });
        this.getParameterList();
      });
    },
    handleParameterFilter(){
      this.parameterListQuery.page = 1;
      this.getParameterList();
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
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
