<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <el-tab-pane label="Admins">
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
            class="filter-item"
            style="margin-left: 10px;"
            type="primary"
            icon="el-icon-edit"
            @click="handleCreate"
          >Add</el-button>
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
          <el-table-column label="Actions" align="center" width="230" class-name="small-padding">
            <template slot-scope="{row}">

              <el-button
                type="primary"
                icon="el-icon-edit"
                circle
                :loading="buttonLoading"
                @click="handleEdit(row)"
              ></el-button>
              <el-button
                type="danger"
                icon="el-icon-delete"
                circle
                :loading="buttonLoading"
                @click="handleDelete(row)"
              ></el-button>
              <el-button icon="el-icon-turn-off"
                circle v-if="row.is_active!=1" type="info" @click="handleModifyStatus(row,1)">
              </el-button>
              <el-button icon="el-icon-open" circle v-if="row.is_active!=0" type="success" @click="handleModifyStatus(row,0)">
              </el-button>
            </template>
          </el-table-column>
          <el-table-column label="Name" min-width="150px">
            <template slot-scope="{row}">
              <span>{{ row.name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Username" min-width="150px">
            <template slot-scope="{row}">
              <span class="link-type" @click="handleEdit(row)">{{ row.username }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Email" min-width="170px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.email }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Contact" width="110px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.contact }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Gender" width="180px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.gender=="f"?'Female':'Male' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="DOB" width="110px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.dob }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Status" class-name="status-col" width="100">
            <template slot-scope="{row}">
              <el-tag :type="row.is_active | statusFilter">{{ row.is_active?'Active':'Deactive' }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Created At" width="150px" align="center">
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
      </el-tab-pane>
      <el-tab-pane label="Roles">
        <div class="filter-container">
          <el-input
            v-model="listRolesQuery.search"
            placeholder="Search Records"
            style="width: 200px;"
            class="filter-item"
            @keyup.enter.native="handleRoleFilter"
          />
          <el-button
            v-waves
            class="filter-item"
            type="primary"
            icon="el-icon-search"
            @click="handleRoleFilter"
          >Search</el-button>
          <el-button
            class="filter-item"
            style="margin-left: 10px;"
            type="primary"
            icon="el-icon-edit"
            @click="handleRoleCreate"
          >Add</el-button>

        </div>

        <el-table
          :key="tableKey"
          v-loading="listLoading"
          :data="roleList"
          border
          fit
          highlight-current-row
          style="width: 100%;"
          @sort-change="roleSortChange"
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
          <el-table-column label="Actions" align="center" width="230" class-name="small-padding">
            <template slot-scope="{row}">

              <el-button
                type="primary"
                icon="el-icon-edit"
                circle
                :loading="buttonLoading"
                @click="handleRoleEdit(row)"
              ></el-button>
            </template>
          </el-table-column>
          <el-table-column label="Name" min-width="150px">
            <template slot-scope="{row}">
              <span>{{ row.name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Created At" width="150px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
            </template>
          </el-table-column>
        </el-table>

        <pagination
          v-show="roleTotal>0"
          :total="roleTotal"
          :page.sync="listRolesQuery.page"
          :limit.sync="listRolesQuery.limit"
          @pagination="getRoleList"
        />
      </el-tab-pane>
      <el-tab-pane label="Permissions">
        <div class="filter-container">
          <el-input
            v-model="listPermissionsQuery.search"
            placeholder="Search Records"
            style="width: 200px;"
            class="filter-item"
            @keyup.enter.native="handlePermissionFilter"
          />
          <el-button
            v-waves
            class="filter-item"
            type="primary"
            icon="el-icon-search"
            @click="handlePermissionFilter"
          >Search</el-button>
          <el-button
            class="filter-item"
            style="margin-left: 10px;"
            type="primary"
            icon="el-icon-edit"
            @click="handlePermissionCreate"
          >Add</el-button>

        </div>

        <el-table
          :key="tableKey"
          v-loading="listLoading"
          :data="permissionList"
          border
          fit
          highlight-current-row
          style="width: 100%;"
          @sort-change="permissionSortChange"
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
          <el-table-column label="Actions" align="center" width="230" class-name="small-padding">
            <template slot-scope="{row}">

              <el-button
                type="primary"
                icon="el-icon-edit"
                circle
                :loading="buttonLoading"
                @click="handlePermissionEdit(row)"
              ></el-button>
            </template>
          </el-table-column>
          <el-table-column label="Name" min-width="150px">
            <template slot-scope="{row}">
              <span>{{ row.name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Created At" width="150px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
            </template>
          </el-table-column>
        </el-table>

        <pagination
          v-show="permissionTotal>0"
          :total="permissionTotal"
          :page.sync="listPermissionsQuery.page"
          :limit.sync="listPermissionsQuery.limit"
          @pagination="getPermissionList"
        />
      </el-tab-pane>
    </el-tabs>

    <el-dialog title="User Details" width="80%" top="2vh" :visible.sync="dialogUserVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="150px" style=" margin-left:50px;">
            <el-row :gutter="20">
              <el-col :span="12">
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
              <el-col :span="12">
                <el-form-item label="Roles" prop="roles">
                  <el-select v-model="temp.roles" filterable multiple placeholder="Select Roles">
                    <el-option
                      v-for="item in roleList"
                      :key="item.id"
                      :label="item.name"
                      :value="item.id">
                    </el-option>
                  </el-select>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="Permissions">
          <el-row style=" height:350px; overflow: auto;">
            <el-checkbox-group v-model="temp.permissions" >
              <el-col :span="8" v-for="permission in permissionList"  :key="permission.id">
                <el-checkbox style="margin-top: 10px;" :label="permission.id" border :key="permission.id">{{permission.name}}</el-checkbox>
              </el-col>
            </el-checkbox-group>
          </el-row>
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

    <el-dialog title="Role Details" width="80%" top="2vh" :visible.sync="dialogRoleVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="dataRoleForm" :rules="roleRules" :model="roleTemp" label-position="left" label-width="150px" style=" margin-left:50px;">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Name" prop="name">
                  <el-input v-model="roleTemp.name" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
        
      </el-tabs>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogRoleVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="dialogStatus==='create'?createRoleData():updateRoleData()">
          Save
        </el-button>
      </div>
    </el-dialog>

    <el-dialog title="Permission Details" width="80%" top="2vh" :visible.sync="dialogPermissionVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="dataPermissionForm" :rules="permissionRules" :model="permissionTemp" label-position="left" label-width="150px" style=" margin-left:50px;">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Name" prop="name">
                  <el-input v-model="permissionTemp.name" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
        
      </el-tabs>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPermissionVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="dialogStatus==='create'?createPermissionData():updatePermissionData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  fetchAdminList,
  createAdminUser,
  updateAdminUser,
  deleteAdminUser,
  changeAdminUserStatus,
  fetchRoleList as roleList,
  createRole,
  updateRole,
  fetchPermissionList,
  createPermission,
  updatePermission,

} from "@/api/superadmin/admins-and-roles";
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
      roleList: null,
      permissionList: null,
      total: 0,
      roleTotal: 0,
      permissionTotal: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        name: undefined,
        username: undefined,
        email: undefined,
        contact: undefined,
        gender: "m",
        roles:[],
        permissions:[],
        dob: undefined,
        is_active: 'all',
        search:undefined,
        sort: "+id"
      },
      listRolesQuery: {
        page: 1,
        limit: 5,
        name:undefined,
        search:undefined,
        sort: "+id"
      },
      listPermissionsQuery: {
        page: 1,
        limit: 5,
        name:undefined,
        search:undefined,
        sort: "+id"
      },
      statusFilter:'all',
      roleList:[],
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
        roles:[],
        permissions:[],
        dob: undefined,
        is_active: 0,
      },
      roleTemp: {
        id: undefined,
        name: undefined,
      },
      permissionTemp: {
        id: undefined,
        name: undefined,
      },
      dialogUserVisible: false,
      dialogRoleVisible:false,
      dialogPermissionVisible:false,
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
      roleRules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
      },
      permissionRules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getRoleList();
    this.getPermissionList();
  },
  methods: {
    getList() {
      this.listLoading = true;
      fetchAdminList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getRoleList(){
      roleList(this.listRolesQuery).then(response=>{
        this.roleList=response.data.data;
        this.roleTotal = response.data.total;
      });
    },
    getPermissionList(){
      fetchPermissionList(this.listPermissionsQuery).then(response=>{
        this.permissionList=response.data.data;
        this.permissionTotal = response.data.total;
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.listQuery.is_active=this.statusFilter;
      this.getList();
    },
    handleRoleFilter() {
      this.listRolesQuery.page = 1;
      this.getRoleList();
    },
    handlePermissionFilter() {
      this.listPermissionsQuery.page = 1;
      this.getPermissionList();
    },
    handleModifyStatus(row, status) {
      let data={'id':row.id,'is_active':status};
      changeAdminUserStatus(data).then((response) => {
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
    roleSortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    permissionSortChange(data) {
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
    roleSortByID(order) {
      if (order === "ascending") {
        this.listRolesQuery.sort = "+id";
      } else {
        this.listRolesQuery.sort = "-id";
      }
      this.handleRoleFilter();
    },
    permissionSortByID(order) {
      if (order === "ascending") {
        this.listPermissionsQuery.sort = "+id";
      } else {
        this.listPermissionsQuery.sort = "-id";
      }
      this.handlePermissionFilter();
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        name: undefined,
        username: undefined,
        email: undefined,
        contact: undefined,
        gender: "m",
        roles:[],
        permissions:[],
        dob: undefined,
        is_active: 0,
      };
    },
    resetRoleTemp() {
      this.roleTemp = {
        id: undefined,
        name: undefined,
      };
    },
     resetPermissionTemp() {
      this.permissionTemp = {
        id: undefined,
        name: undefined,
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
          createAdminUser(this.temp).then((response) => {
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
      var keys = [];

      row.roles.map(role => {
          keys.push(role.id);
      })

      keys = keys.filter((item, i, ar) => ar.indexOf(item) === i);

      this.temp.roles=keys;

      var permissionsKeys = [];

      row.permissions.map(permission => {
          permissionsKeys.push(permission.id);
      })

      this.temp.permissions=permissionsKeys;

      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          const tempData = Object.assign({}, this.temp);
          updateAdminUser(tempData).then((response) => {
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
      deleteAdminUser(row.id).then((data) => {
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

    handleRoleCreate() {
      this.resetRoleTemp();
      this.dialogStatus = "create";
      this.dialogRoleVisible = true;
      this.$nextTick(() => {
        this.$refs["dataRoleForm"].clearValidate();
      });
    },
    createRoleData() {
      this.$refs["dataRoleForm"].validate(valid => {
        if (valid) {
          createRole(this.roleTemp).then((response) => {
            this.roleList.unshift(response.data);
            this.dialogRoleVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          })
        }
      });
    },
    handleRoleEdit(row) {
      this.roleTemp = Object.assign({}, row);
      this.dialogStatus = "update";
      this.dialogRoleVisible = true;
      this.$nextTick(() => {
        this.$refs["dataRoleForm"].clearValidate();
      });
    },
    updateRoleData() {
      this.$refs["dataRoleForm"].validate(valid => {
        if (valid) {
          const tempData = Object.assign({}, this.roleTemp);
          updateRole(tempData).then((response) => {
            for (const v of this.roleList) {
              if (v.id === this.roleTemp.id) {
                const index = this.roleList.indexOf(v);
                this.roleList.splice(index, 1, response.data);
                break;
              }
            }
            this.dialogRoleVisible = false;
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

    handlePermissionCreate() {
      this.resetPermissionTemp();
      this.dialogStatus = "create";
      this.dialogPermissionVisible = true;
      this.$nextTick(() => {
        this.$refs["dataPermissionForm"].clearValidate();
      });
    },
    createPermissionData() {
      this.$refs["dataPermissionForm"].validate(valid => {
        if (valid) {
          createPermission(this.permissionTemp).then((response) => {
            this.permissionList.unshift(response.data);
            this.dialogPermissionVisible = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          })
        }
      });
    },
    handlePermissionEdit(row) {
      this.permissionTemp = Object.assign({}, row);
      this.dialogStatus = "update";
      this.dialogPermissionVisible = true;
      this.$nextTick(() => {
        this.$refs["dataPermissionForm"].clearValidate();
      });
    },
    updatePermissionData() {
      this.$refs["dataPermissionForm"].validate(valid => {
        if (valid) {
          const tempData = Object.assign({}, this.permissionTemp);
          updatePermission(tempData).then((response) => {
            for (const v of this.permissionList) {
              if (v.id === this.permissionTemp.id) {
                const index = this.permissionList.indexOf(v);
                this.permissionList.splice(index, 1, response.data);
                break;
              }
            }
            this.dialogPermissionVisible = false;
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
          filename: "users"
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
</style>
