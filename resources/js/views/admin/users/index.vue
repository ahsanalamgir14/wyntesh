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
      <el-table-column label="Actions" align="center" width="170" class-name="small-padding">
        <template slot-scope="{row}">

          <el-button
            type="primary"
            icon="el-icon-edit"
            circle
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
          <el-button icon="el-icon-turn-off"
            circle v-if="row.is_active!=1" type="info" @click="handleModifyStatus(row,1)">
          </el-button>
          <el-button icon="el-icon-open" circle v-if="row.is_active!=0" type="success" @click="handleModifyStatus(row,0)">
          </el-button>
        </template>
      </el-table-column>
       <el-table-column label="ID" width="100px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.username }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Name" min-width="120px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
     
      <el-table-column label="Contact" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.contact }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Balance" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.member?row.member.wallet_balance:'0.00' }}</span>
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

       <el-table-column label="Status" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_active | statusFilter">{{ row.is_active?'Active':'Deactive' }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Joining Date" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
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
              <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>

                <el-form-item label="Username" prop="username">
                  <el-input :disabled="this.dialogStatus!='create'" v-model="temp.username" />
                </el-form-item>

                <el-form-item label="Email" prop="email">
                  <el-input type="email" :disabled="this.dialogStatus!='create'" v-model="temp.email" />
                </el-form-item>

                <el-form-item label="Password" prop="password">
                  <el-input type="password" v-model="temp.password" />
                </el-form-item>
              </el-col>
              <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                
                <el-form-item label="Contact" prop="contact">
                  <el-input v-model="temp.contact" />
                </el-form-item>

                <el-form-item label="Gender" prop="gender">
                  <el-radio-group v-model="temp.gender">
                    <el-radio label="m">Male</el-radio>
                    <el-radio label="f">Female</el-radio>
                  </el-radio-group>
                </el-form-item>

                <el-form-item label="DOB" prop="dob">
                  <el-date-picker
                    v-model="temp.dob"
                    type="date"
                    format="yyyy-MM-dd"
                    value-format="yyyy-MM-dd"
                    placeholder="Date of birth">
                  </el-date-picker>
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
        <el-button type="primary" @click="dialogStatus==='create'?createData():updateData()">
          Save
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
  updateExpireDate
} from "@/api/admin/users";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";

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
      listQuery: {
        page: 1,
        limit: 5,
        name: undefined,
        username: undefined,
        email: undefined,
        contact: undefined,
        gender: "m",
        dob: undefined,
        is_active: 'all',
        sort: "+id"
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
      },
      dialogUserVisible: false,
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
      this.listQuery.is_active=this.statusFilter;
      this.getList();
    },
    handleModifyStatus(row, status) {
      let data={'id':row.id,'is_active':status};
      changeUserStatus(data).then((response) => {
        this.$notify({
          title: "Success",
          message: "Status changed Successfully",
          type: "success",
          duration: 2000
        })
      })

      row.is_active = status;
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
        username: undefined,
        email: undefined,
        contact: undefined,
        gender: "m",
        dob: undefined,
        is_active: 0,
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
          createUser(this.temp).then((response) => {
            this.list.unshift(response.data);
            this.dialogUserVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          }).catch((res)=>{

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
          const tempData = Object.assign({}, this.temp);
          updateUser(tempData).then((response) => {
            for (const v of this.list) {
              if (v.id === this.temp.id) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, response.data);
                break;
              }
            }
            this.dialogUserVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
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
