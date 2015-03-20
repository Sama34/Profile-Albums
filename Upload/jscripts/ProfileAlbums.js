var ProfileAlbums = {
	init: function()
	{
		if(radioprofilealbums == "usercp")
		{
			$('PcNewAlbum').hide();
			$('UrlNewAlbum').hide();
		}
		else if(radioprofilealbums == "editalbum")
		{
			$('PcEditAlbum').hide();
			$('UrlEditAlbum').hide();
		}
		else if(radioprofilealbums == "uploadimage")
		{
			$('PcNewimage').hide();
			$('UrlNewimage').hide();
		}
		else if(radioprofilealbums == "editimage")
		{
			$('PcEditImage').hide();
			$('UrlEditImage').hide();
		}
	},
	PcNewAlbum: function()
	{
		$('PcNewAlbum').show();
		$('UrlNewAlbum').hide();
	},
	UrlNewAlbum: function()
	{
		$('UrlNewAlbum').show();
		$('PcNewAlbum').hide();
	},
	PcEditAlbum: function()
	{
		$('PcEditAlbum').show();
		$('UrlEditAlbum').hide();
	},
	UrlEditAlbum: function()
	{
		$('UrlEditAlbum').show();
		$('PcEditAlbum').hide();
	},
	NoneEditAlbum: function()
	{
		$('UrlEditAlbum').hide();
		$('PcEditAlbum').hide();
	},
	PcNewImage: function()
	{
		$('PcNewimage').show();
		$('UrlNewimage').hide();
	},
	UrlNewImage: function()
	{
		$('UrlNewimage').show();
		$('PcNewimage').hide();
	},
	PcEditImage: function()
	{
		$('PcEditImage').show();
		$('UrlEditImage').hide();
	},
	UrlEditImage: function()
	{
		$('UrlEditImage').show();
		$('PcEditImage').hide();
	},
	NoneEditImage: function()
	{
		$('UrlEditImage').hide();
		$('PcEditImage').hide();
	}
};
Event.observe(document, 'dom:loaded', ProfileAlbums.init);