import { mount } from 'vue-test-utils';
import expect from 'expect';
import Test from '../../resources/js/components/test.vue';

describe ('Test', () => {
	it ('Testea los tests', () => {
		let wrapper = mount(Test);
		expect(wrapper.vm.test).toBe(true);
	})

});