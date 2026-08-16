(function (blocks, element, components, blockEditor, serverSideRender) {
   
    const el = element.createElement;
    const ServerSideRender = serverSideRender;    
    const { InspectorControls } = blockEditor;
    const { SelectControl, PanelBody } = components;

    blocks.registerBlockType('ymc-filter-grids/filter', {
         title: 'YMC Filter',
         icon: el('img', { 
            src: ymcBlockData.iconUrl, 
            style: { 
               width: '20px', 
               height: '20px',
               display: 'block'
            } 
         }),
        category: 'widgets',
        attributes: {
            filterId: {
                type: 'string',
                default: ''
            }
        },
       
        edit: (props) => {
            
            const { attributes, setAttributes, className } = props;
            const { filterId } = attributes;
            
            const inspectorControls = el(
                InspectorControls,
                null,
                el(
                    PanelBody,
                    { title: 'Filter Settings', initialOpen: true },
                    el(SelectControl, {
                        label: 'Select Filter',
                        value: filterId,
                        options: ymcBlockData.filters,                        
                        onChange: (newVal) => setAttributes({ filterId: newVal })
                    })
                )
            );
            
            const blockPreview = el(
                ServerSideRender,
                {
                    block: 'ymc-filter-grids/filter',
                    attributes: attributes,
                    emptyResponsePlaceholder: () => el('p', {}, 'Loading filter...')
                }
            );

            return el('div', { className: className }, inspectorControls, blockPreview);
        },
        
        save: () => null
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor,
    window.wp.serverSideRender
);

