// Menu
export interface Menu {
  path?: string;
  title?: string;
  type?: string;
  megaMenu?: boolean;
  megaMenuType?: string; // small, medium, large
  image?: string;
  children?: Menu[];
}

export const MENUITEMS: Menu[] = [
	{
		title: 'home', type: 'link', path: 'home/one',
	},
	{
		title: 'products', type: 'link', path: '/home/left-sidebar/collection/electronics'
	},
	{
		 path: '/pages/about-us', title: 'about-us', type: 'link'
	},
	{
		 path: '/pages/contact', title: 'contact-us', type: 'link'
	},

]