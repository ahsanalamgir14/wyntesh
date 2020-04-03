import axios from 'axios';
import { Message } from 'element-ui';
import { getToken, setToken } from '@/utils/auth';

// Create axios instance
const service = axios.create({
  baseURL: process.env.MIX_BASE_API,
  timeout: 100000, // Request timeout
});

// Request intercepter
service.interceptors.request.use(
  config => {
    const token = getToken();
    if (token) {
      config.headers['Authorization'] = 'Bearer ' + getToken(); // Set JWT token
    }

    return config;
  },
  error => {
    // Do something with request error
    console.log(error); // for debug
    Promise.reject(error);
  }
);

// response pre-processing
service.interceptors.response.use(
  response => {
    if (response.headers.authorization) {
      setToken(response.headers.authorization);
      response.data.token = response.headers.authorization;
    }

    return response.data;
  },
  error => {
    if(typeof error.response !== 'undefined'){
      if(error.response.data.message =='Validation error'){
        let errorMessage='';
        let errorOb=error.response.data.data;
        for (var prop in errorOb) {
          if (!errorOb.hasOwnProperty(prop)) continue;
          errorOb[prop].forEach(pr => {
            errorMessage=errorMessage+pr+'</br>';
          });
        }
        Message({
          dangerouslyUseHTMLString: true,
          message:errorMessage,
          type: 'error',
          duration: 2 * 3000
        })
      }else{
        Message({
          message: error.response.data.message,
          type: 'error',
          duration: 2 * 1000
        })
      }
    }else{
      Message({
        message: 'Something went wrong.',
        type: 'error',
        duration: 2 * 1000
      })
    }
    return Promise.reject(error);
  },
);

export default service;
