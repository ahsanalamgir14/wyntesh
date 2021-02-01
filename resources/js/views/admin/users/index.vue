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
      <el-select v-model="statusFilter" style="width: 140px" placeholder="Status" class="filter-item" @change="handleFilter">
        <el-option  key="1200" label="All" value="all" />
        <el-option  key="1201" label="Active" value="1" />
        <el-option  key="1202" label="Deactive" value="0" />
      </el-select>
      <el-select v-model="listQuery.is_blocked" style="width: 140px" clearable placeholder="Blocked ?" class="filter-item" @change="handleFilter">        
        <el-option  key="1201" label="Blocked" value="blocked" />
        <el-option  key="1202" label="Unblocked" value="unblocked" />
      </el-select>
      <el-select v-model="listQuery.kyc_status" style="width: 140px" clearable placeholder="KYC Status" class="filter-item" @change="handleFilter">        
        <el-option  key="1201" label="Pending" value="pending" />
        <el-option  key="1202" label="Submitted" value="submitted" />
        <el-option  key="1203" label="Rejected" value="rejected" />
        <el-option  key="1204" label="Verified" value="verified" />
      </el-select>
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
        label="Sr#"
        prop="id"
        sortable="custom"
        align="center"
        width="70"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="190" class-name="small-padding">
        <template slot-scope="{row}">

          <el-button
            type="primary"
            
            circle
            icon="el-icon-edit"
            :loading="buttonLoading"
            @click="handleEdit(row)"
          ></el-button>
          <!-- <el-button
            type="danger"
            icon="el-icon-delete"
            circle
            :loading="buttonLoading"
            @click="handleDelete(row)"
          ></el-button> -->
          <el-tooltip content="Unblock" placement="top">
            <el-button icon="el-icon-check"
              circle v-if="row.is_blocked==1" type="success" @click="handleModifyStatus(row,0)">
            </el-button>
          </el-tooltip>
          <el-tooltip content="Block" placement="top">
            <el-button icon="el-icon-close" circle v-if="row.is_blocked==0" type="danger" @click="handleModifyStatus(row,1)">
            </el-button>
          </el-tooltip>
          <el-button icon="el-icon-turn-off"
            circle v-if="row.is_active!=1" type="info" @click="handleModifyActivationStatus(row,1)">
          </el-button>
          <el-button icon="el-icon-open" circle v-if="row.is_active!=0" type="success" @click="handleModifyActivationStatus(row,0)">
          </el-button>
        </template>
      </el-table-column>

       <el-table-column label="ID" width="100px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.username }}</span>
        </template>
      </el-table-column>

       <el-table-column label="Login" width="100px">
        <template slot-scope="{row}">
          <span class="link-type" ><a :href="loginLink(row)">Login</a></span>
        </template>
      </el-table-column>

      <el-table-column label="Name" min-width="120px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Rank" min-width="120px">
        <template slot-scope="{row}">
          <el-tag type="primary">{{ row.member.rank.name }}</el-tag>
        </template>
      </el-table-column>
     
      <el-table-column label="Contact" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.contact }}</span>
        </template>
      </el-table-column>


      <el-table-column label="Parent" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.parent?row.member.parent.user.name:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Parent ID" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.parent?row.member.parent.user.username:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Sponsor" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.sponsor?row.member.sponsor.user.name:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Sponsor ID" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.sponsor?row.member.sponsor.user.username:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Balance" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.member?row.member.wallet_balance:'0.00' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Joining Date" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>

      <el-table-column label="KYC" width="110px" align="center">
        <template slot-scope="{row}">
          <span v-if="row.member.kyc">
            <el-tag v-if="row.member.kyc.verification_status=='verified'" type="success">Verified</el-tag>
            <el-tag v-if="row.member.kyc.verification_status=='pending'" type="warning">Pending</el-tag>
            <el-tag v-if="row.member.kyc.verification_status=='submitted'" type="primary">Submitted</el-tag>
            <el-tag v-if="row.member.kyc.verification_status=='rejected'" type="danger">Rejected</el-tag>
          </span>
        </template>
      </el-table-column>

       <el-table-column label="Group Business" width="140px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.group_pv }}</span>
        </template>
      </el-table-column>

       <el-table-column label="Status" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_active | statusFilter">{{ row.is_active?'Active':'Deactive' }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Is Blocked" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_blocked | statusFilter">{{ row.is_blocked?'Yes':'No' }}</el-tag>
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
    <el-dialog title="User Details" width="80%" top="2vh" :visible.sync="dialogUserVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="dataForm" :rules="rules" :model="temp" style="">
            <el-row :gutter="20">
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>

                <el-form-item label="Username" prop="username">
                  <el-input :disabled="this.dialogStatus!='create'" v-model="temp.username" />
                </el-form-item>

                <el-form-item label="Email" prop="email">
                  <el-input type="email"  v-model="temp.email" />
                </el-form-item>

                <el-form-item label="Password" prop="password">
                  <el-input type="password" v-model="temp.password" />
                </el-form-item>
              </el-col>
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                
                <el-form-item label="Contact" prop="contact">
                  <el-input v-model="temp.contact" />
                </el-form-item>

                <el-form-item label="Gender" prop="gender">
                  <br>
                  <el-radio-group  v-model="temp.gender">
                    <el-radio label="m" border>Male</el-radio>
                    <el-radio label="f" border>Female</el-radio>
                  </el-radio-group>
                </el-form-item>

                <el-form-item label="DOB" prop="dob">
                  <br>
                  <el-date-picker
                    v-model="temp.dob"
                    type="date"
                    format="yyyy-MM-dd"
                    value-format="yyyy-MM-dd"
                    placeholder="Date of birth">
                  </el-date-picker>
                </el-form-item>
                
              </el-col>
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <el-form-item label="Personal PV" prop="total_personal_pv">
                  <el-input v-model="temp.member.total_personal_pv" />
                </el-form-item>
                <el-form-item label="Rank" prop="total_personal_pv">
                  <br>
                  <el-select v-model="temp.member.rank_id" class="filter-item " style="width: 100%" filterable placeholder="Select Rank">
                    <el-option v-for="item in ranks" :key="item.name" :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
      </el-tabs>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogUserVisible = false">
          Cancel
        </el-button>
        <el-button  icon="el-icon-finished" :loading="buttonLoading" type="primary" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>

    <el-dialog title="User Activation Status" width="40%" top="30px"  :visible.sync="dialogUserActivationStatus">
      <el-form ref="userStatusForm" :model="userStatusLog"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            
            <el-form-item label="Remarks" prop="remarks">
              <el-input  type="textarea" :cols="2" v-model="userStatusLog.remarks" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogUserActivationStatus = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="updateUserActivationStatus()">
          Update
        </el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import {
  fetchList,
  createUser,
  updateUser,
  deleteUser,
  changeUserStatus,
  updateExpireDate,
  changeUserActivationStatus
} from "@/api/admin/users";

import {
  getAllRanks,
} from "@/api/admin/ranks";

import waves from "@/directive/waves"; // waves directive
import { getToken } from '@/utils/auth';
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import defaultSettings from '@/settings';
const { baseUrl } = defaultSettings;

export default {
  name: "ComplexTable",
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
      ranks:[],
      listQuery: {
        page: 1,
        limit: 10,
        name: undefined,
        username: undefined,
        email: undefined,
        contact: undefined,
        gender: "m",
        dob: undefined,
        is_active: 'all',
        sort: "-id"
      },
      statusFilter:'all',
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      statusOptions: ["published", "draft", "deleted"],
      showReviewer: false,
      temp: {
        id: undefined,
        name: undefined,
        username: undefined,
        email: undefined,
        password:undefined,
        contact: undefined,
        gender: "m",
        dob: undefined,
        is_active: 0,
        member:{
          total_personal_pv: 0,
        }
      },
      userStatusLog:{
        user_id:undefined,
        is_active:0,
        remarks:undefined,
      },
      dialogUserVisible: false,
      dialogUserActivationStatus:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
        username: [
          { required: true, message: "Username required", trigger: "blur" }
        ],
        email:[
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] }
        ]
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
      getAllRanks().then(response => {
        this.ranks = response.data;
      });
    },
    loginLink(row){
      return baseUrl+'#/login?token='+getToken()+'&username='+row.username;
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.listQuery.is_active=this.statusFilter;
      this.getList();
    },
    handleModifyStatus(row, status) {
      let data={'id':row.id,'is_blocked':status};
      changeUserStatus(data).then((response) => {
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      })

      row.is_blocked = status;
    },
    handleModifyActivationStatus(row, status) {
      this.resetUserStatus();
      let temp = Object.assign({}, row);
      this.userStatusLog.user_id=temp.id;
      this.userStatusLog.is_active=status;
      this.dialogUserActivationStatus=true;
    },
    updateUserActivationStatus(){
      changeUserActivationStatus(this.userStatusLog).then((response) => {
        this.getList();
        this.dialogUserActivationStatus=false;
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      })

    },
    resetUserStatus(){
      this.userStatusLog = {
        user_id:undefined,
        is_active:0,
        remarks:undefined,
      };
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        name: undefined,
        username: undefined,
        email: undefined,
        contact: undefined,
        gender: "m",
        dob: undefined,
        is_active: 0,
        member:{
          total_personal_pv: 0,
        }
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogUserVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          createUser(this.temp).then((response) => {
            this.list.unshift(response.data);
            this.dialogUserVisible = false;
            this.buttonLoading=false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          }).catch((res)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleEdit(row) {
      this.temp = Object.assign({}, row);
      
      this.dialogStatus = "update";
      this.dialogUserVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          const tempData = Object.assign({}, this.temp);
          updateUser(tempData).then((response) => {
            this.getList();
            this.buttonLoading=false;
            this.dialogUserVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
          }).catch((res)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleDelete(row) {
      deleteUser(row.id).then((data) => {
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
    cancelEdit(row) {
      row.pivot.expire_date = row.pivot.originalExpireDate
      row.edit = false
    },
    editExpireDate(row){
      row.edit=!row.edit
      row.pivot.originalExpireDate=row.pivot.expire_date;
    },
    confirmEdit(row) {
      row.edit = false
      
      updateExpireDate(data).then((data) => {
         this.$message({
          message: 'Expire date has been updated',
          type: 'success'
        })
      });
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Name",
          "Username",
          "Email",
          "Contact",
          "Gender",
          "DOB",
          "Status",
          "Created at"
        ];
        const filterVal = [
          "id",
          "name",
          "username",
          "email",
          "contact",
          "gender",
          "dob",
          "is_active",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "members"
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
.el-table--medium th, .el-table--medium td {
    padding: 0px 0;
}
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
</style>
