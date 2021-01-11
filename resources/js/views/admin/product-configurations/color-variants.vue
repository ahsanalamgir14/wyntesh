<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item mobile_class" size="mini" @keyup.enter.native="handleFilter" />
      <el-button v-waves size="mini" class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button size="mini" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">Add</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="150" class-name="small-padding">
        <template slot-scope="{row}">
          <el-tooltip content="Edit" placement="right" effect="dark">
            <el-button type="primary" circle icon="el-icon-edit" :loading="buttonLoading" @click="handleEdit(row)"></el-button>
          </el-tooltip>
          <el-tooltip content="Delete" placement="right" effect="dark">
            <el-button type="danger" icon="el-icon-delete" circle :loading="buttonLoading" @click="handleDelete(row)"></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Color Code" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.color_code }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Note" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.note }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="'Color '+textMap[dialogStatus]" :fullscreen="true" width="80%" top="2vh" :visible.sync="dialogSizeVisible">
      <el-form ref="colorVariantsForm" :rules="rules" :model="temp">
        <el-tabs type="border-card">
          <el-tab-pane label="Details">
            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>
                <el-form-item label="Color Code" prop="color_code">
                  <el-input v-model="temp.color_code" />
                </el-form-item>
                <el-form-item label="Note" prop="note">
                  <el-input type="textarea" v-model="temp.note" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogSizeVisible = false">
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
  createColor,
  updateColor,
  deleteColor,
  changeColorStatus
} from "@/api/admin/color-variants";
import { parse_currency } from "@/utils/currencies";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import role from '@/directive/role'; // Permission directive (v-role)

export default {
  name: "Packages",
  components: { Pagination },
  directives: { waves, role },
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
      list: [],
      total: 3,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        color_code: undefined,
        note: undefined,

      },
      rules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
        color_code: [
          { required: true, message: "Color Code is required", trigger: "blur" }
        ],
      },
      dialogSizeVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      fileList: [],
      file: undefined,
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
    resetTemp() {
      this.temp = {
        name: undefined,
        color_code: undefined,
        note: undefined,
      };
      this.file = undefined;
      this.fileList = [];
    },
    handleModifyStatus(row, status) {
      let data = { 'id': row.id, 'is_active': status };
      changeColorStatus(data).then((response) => {
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      })
      row.is_active = status;
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogSizeVisible = true;
      this.$nextTick(() => {
        this.$refs["colorVariantsForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["colorVariantsForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          const form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }
          form.append('image', this.file);
          createColor(form).then((response) => {
            this.getList();
            this.dialogSizeVisible = false;
            this.buttonLoading = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          }).catch((res) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleEdit(row) {
      this.file = undefined;
      this.fileList = [];
      this.temp = Object.assign({}, row);
      this.dialogStatus = "update";
      this.dialogSizeVisible = true;

      this.$nextTick(() => {
        this.$refs["colorVariantsForm"].clearValidate();
      });
    },
    updateData() {
      this.$refs["colorVariantsForm"].validate(valid => {
        if (valid) {

          var form = new FormData();
          const form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);
          this.buttonLoading = true;
          updateColor(form, form_data.id).then((response) => {
            this.getList();
            this.buttonLoading = false;
            this.dialogSizeVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
          }).catch((res) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleDelete(row) {
      deleteColor(row.id).then((data) => {
        this.$notify({
          title: "Success",
          message: "Delete Successfully",
          type: "success",
          duration: 2000
        });
        this.getList();
      });
    },
    handleChange(f, fl) {
      if (fl.length > 1) {
        fl.shift();
      }
      this.file = f.raw;
    },
    handleRemove(file, fileList) {
      this.file = undefined;
      this.fileList = [];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
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
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
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
