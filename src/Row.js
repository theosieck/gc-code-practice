import Grid from '@material-ui/core/Grid';

const Row = (props) => (
    <Grid container direction="row" justify="space-between" alignItems="center">
    <Grid item xs={8} zeroMinWidth><p>{props.selection}</p></Grid>
    <Grid item xs={4} zeroMinWidth><Grid container direction="column" justify="flex-start" alignItems="center">{props.code}</Grid></Grid>
    </Grid>
)

export default Row;